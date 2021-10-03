<?php

namespace App\Http\Controllers;

use App\Imports\BulkImport;
use App\Models\Area;
use App\Models\Branch;
use App\Models\City;
use App\Models\CourierRequest;
use App\Models\CourierType;
use App\Models\DeliveryMode; 
use App\Models\PackagingType;
use App\Models\Pricing;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class BulkrequestsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
//        if($user && $user->type == 'admin'){ // Temporary fix for admin
        if(!$user || $user->type == 'admin'){
            $courier_requests = CourierRequest::with('delivery_mode','packaging_type','receiver_area','sender_area','branch',
                'receiver_city','sender_city','courier_type','status','pricing')->get();
        }else if($user->type == 'delivery_rider'){
            $courier_requests = CourierRequest::where('rider_id', Auth::id())->get();
        }else if($user->type == 'pickup_rider'){
            $courier_requests = CourierRequest::where('rider_id', Auth::id())->get();
        }else if($user->type == 'individual'){
            $courier_requests = CourierRequest::where('customer_id', Auth::id())->get();
        }else if($user->type == 'merchant'){
            $courier_requests = CourierRequest::where('customer_id', Auth::id())->get();
        }
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $courier_requests;
        $statuses = Status::all();
        if ($request->is('dashboard/*')) {
            return view('bulkRequests.index')
                ->with('statuses',$statuses)
                ->with('courier_requests',$res->data);
        }
        return response()->json($res, 200);
    }

    public function create(){
        $courier_types = CourierType::all();
        $cities = City::all();
        $areas = Area::all();
        $package_types = PackagingType::all();
        $delivery_modes = DeliveryMode::all();
        $pricings = Pricing::all();
        $statuses = Status::all();
        return view('bulkRequests.create')
            ->with('courier_types',$courier_types)
            ->with('cities',$cities)
            ->with('areas',$areas)
            ->with('package_types',$package_types)
            ->with('delivery_modes',$delivery_modes)
            ->with('pricings',$pricings)
            ->with('statuses',$statuses);
    }


    public function edit($id){
        $courier_request = CourierRequest::find($id);
        $courier_types = CourierType::all();
        $cities = City::all();
        $areas = Area::all();
        $package_types = PackagingType::all();
        $delivery_modes = DeliveryMode::all();
        $pricings = Pricing::all();
        $statuses = Status::all();
        $riders = User::where('type','delivery_rider')->orwhere('type','pickup_rider')->get();
        return view('courierRequests.edit')
            ->with('courier_request',$courier_request)
            ->with('courier_types',$courier_types)
            ->with('cities',$cities)
            ->with('areas',$areas)
            ->with('riders',$riders)
            ->with('package_types',$package_types)
            ->with('delivery_modes',$delivery_modes)
            ->with('pricings',$pricings)
            ->with('statuses',$statuses);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'courier_type_id' => 'integer|required|exists:courier_types,id',
            'sender_city_id' => 'integer|required|exists:cities,id',
            'receiver_city_id' => 'integer|required|exists:cities,id',
            'sender_area_id' => 'integer|required|exists:areas,id',
            'receiver_area_id' => 'integer|required|exists:areas,id',
            'packaging_type_id' => 'integer|required|exists:packaging_types,id',
            'delivery_mode_id' => 'integer|required|exists:delivery_modes,id',
            'sender_address' => 'string|required',
            'receiver_address' => 'string|required',
            'receiver_name' => 'string|required',
            'sender_name' => 'string|required',
            'sender_phone' => 'string|required',
            'note' => 'string',
            'receiver_phone' => 'string|required',
            'fragile' => 'boolean|required',
            'paid_by' => 'string|in:receiver,sender,merged_with_cod',
            'cash_on_delivery' => 'boolean|required',
            'cash_on_delivery_amount' => 'numeric|required_if:cash_on_delivery,1|required_if:paid_by,merged_with_cod',
            'approximate_weight' => 'numeric|required',
            'pricing_id' => 'integer|required|exists:pricings,id'
        ]);
        $status = Status::where('sequence', 1)->first();
        $validated['status_id'] = $status ? $status->id : 0;
        $validated['actual_weight'] = $validated['approximate_weight'];
        $validated['customer_id'] = Auth::id() ? Auth::id() : 0;
        $validated['rider_id'] = Auth::id() ? Auth::id() : 0;
        $branch = Branch::where('area_id', $validated['sender_area_id'])->first();
        $validated['branch_id'] = $branch ? $branch->id : null;
        if(!$validated['branch_id']){
            $branch = Branch::where('city_id', $validated['sender_city_id'])->first();
            $validated['branch_id'] = $branch ? $branch->id : null;
        }
        if(!$validated['branch_id']){
            $validated['branch_id'] = 0;
        }
        $res = new \stdClass();
        try{
            $courier_request = CourierRequest::create($validated);
            $res->error = false;
            $res->message = "Courier request sent!";
            $res->data = [$courier_request];

            if ($request->is('dashboard/*')) {
                return redirect('/dashboard/courier_bulk_request')->with('message','Courier bulk request created');
            }

            return response()->json($res, 201);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            return response()->json($res, 500);
        }
    }

    public function store_api(Request $request)
    {
        $validated = $request->validate([
            'courier_type_id' => 'integer|required|exists:courier_types,id',
            'sender_city_id' => 'integer|required|exists:cities,id',
            'receiver_city_id' => 'integer|required|exists:cities,id',
            'sender_area_id' => 'integer|required|exists:areas,id',
            'receiver_area_id' => 'integer|required|exists:areas,id',
            'packaging_type_id' => 'integer|required|exists:packaging_types,id',
            'delivery_mode_id' => 'integer|required|exists:delivery_modes,id',
            'sender_name' => 'string|required',
            'sender_phone' => 'string|required',
            'sender_address' => 'string|required',
            'receiver_address' => 'string|required',
            'receiver_name' => 'string|required',
            'note' => 'string',
            'receiver_phone' => 'string|required',
            'fragile' => 'boolean|required',
            'paid_by' => 'string|in:receiver,sender,merged_with_cod',
            'cash_on_delivery' => 'boolean|required',
            'cash_on_delivery_amount' => 'numeric|required_if:cash_on_delivery,1|required_if:paid_by,merged_with_cod',
            'approximate_weight' => 'numeric|required',
            'pricing_id' => 'integer|required|exists:pricings,id'
        ]);
        $status = Status::where('sequence', 1)->first();
        $validated['status_id'] = $status ? $status->id : 0;
        $validated['actual_weight'] = $validated['approximate_weight'];
        $validated['customer_id'] = Auth::id() ? Auth::id() : 0;
        $validated['rider_id'] = Auth::id() ? Auth::id() : 0;
        $branch = Branch::where('area_id', $validated['sender_area_id'])->first();
        $validated['branch_id'] = $branch ? $branch->id : null;
        if(!$validated['branch_id']){
            $branch = Branch::where('city_id', $validated['sender_city_id'])->first();
            $validated['branch_id'] = $branch ? $branch->id : null;
        }
        if(!$validated['branch_id']){
            $validated['branch_id'] = 0;
        }
        $res = new \stdClass();
        try{
            $courier_request = CourierRequest::create($validated);
            $res->error = false;
            $res->message = "Courier request sent!";
            $res->data = [$courier_request];

            if ($request->is('dashboard/*')) {
                return redirect('/dashboard/courier_bulk_request')->with('message','Courier bulk request created');
            }

            return response()->json($res, 201);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            return response()->json($res, 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show(Request $request, $id)
    {
        $rec = CourierRequest::with('delivery_mode','packaging_type','receiver_area','sender_area','branch','receiver_city',
            'sender_city','courier_type','status','pricing','customer','rider')->where('id',$id)->first();
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier bulk Request Loaded";
        $res->data = [$rec];

        if ($request->is('dashboard/*')){
            return view('bulkRequests.show')->with('courier_request',$rec);
        }
        return response()->json($res, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'courier_type_id' => 'integer|exists:courier_types,id',
            'sender_city_id' => 'integer|exists:cities,id',
            'receiver_city_id' => 'integer|exists:cities,id',
            'sender_area_id' => 'integer|exists:areas,id',
            'receiver_area_id' => 'integer|exists:areas,id',
            'packaging_type_id' => 'integer|exists:packaging_types,id',
            'delivery_mode_id' => 'integer|exists:delivery_modes,id',
            'sender_name'=>'string',
            'sender_phone'=>'string',
            'sender_address' => 'string',
            'receiver_address' => 'string',
            'receiver_name' => 'string',
            'note' => 'string',
            'receiver_phone' => 'string',
            'fragile' => 'boolean',
            'paid_by' => 'string|in:receiver,sender,merged_with_cod',
            'cash_on_delivery' => 'boolean',
            'cash_on_delivery_amount' => 'numeric|required_if:cash_on_delivery,1|required_if:paid_by,merged_with_cod',
            'approximate_weight' => 'numeric|required',
            'pricing_id' => 'integer|exists:pricings,id'
        ]);
        $validated['actual_weight'] = $validated['approximate_weight'];
        $res = new \stdClass();
        $courier_request = CourierRequest::findOrFail($id);
        try{
            $courier_request->update($validated);
            $res->error = false;
            $res->message = "Courier bulk request updated!";
            $res->data = [$courier_request];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/courier_bulk_request')->with('message',$res->message);
            }
            return response()->json($res, 201);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            return response()->json($res, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy(Request $request, $id)
    {
        $courier_request = CourierRequest::findOrFail($id);
        $res = new \stdClass();
        try{
            $courier_request->delete();
            $res->error = false;
            $res->message = "Courier bulk Request Deleted!";
            $res->data = [$courier_request];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/courier_bulk_request')->with('message',$res->message);
            }
            return response()->json($res, 204);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            return response()->json($res, 500);
        }
    }

    public function get_pricing(Request $request){
        $validated = $request->validate([
            'courier_type_id' => 'integer|required|exists:courier_types,id',
            'sender_city_id' => 'integer|required|exists:cities,id',
            'receiver_city_id' => 'integer|required|exists:cities,id',
            'delivery_mode_id' => 'integer|required|exists:delivery_modes,id',
            'approximate_weight' => 'numeric|required|min:0.1',
        ]);
        $pricing = Pricing::where('min_weight', '<', $validated['approximate_weight'])
            ->where('max_weight', '>=', $validated['approximate_weight'])
            ->where('courier_type_id', $validated['courier_type_id'])
            ->where('sender_city_id', $validated['sender_city_id'])
            ->where('receiver_city_id', $validated['receiver_city_id'])
            ->where('delivery_mode_id', $validated['delivery_mode_id'])->first();
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Pricing loaded";
        $res->data = $pricing;
        return response()->json($res, 200);
    }

    public function update_status(Request $request, $id){
        $validated = $request->validate([
            'status_id' => 'integer|required|exists:statuses,id'
        ]);
        $status = Status::findOrFail($validated['status_id']);
        if(!$status){
            return BlendxHelpers::generate_response(true, "Status with id: ".$validated['status_id']." not found", []);
        }
        $courier_request = CourierRequest::findOrFail($id);
        $courier_request->status_id = $validated['status_id'];
        $courier_request->save();
        if ($request->is('dashboard/*')){
            return redirect('/dashboard/courier_bulk_request')->with('message','Courier bulk request status updated');
        }
        return response()->json(BlendxHelpers::generate_response(false, 'Status updated!', []));
    }

    public function upload(Request $request){

        $request->validate([
            "file_data" => "required|mimes:xls,xlsx",
        ]);
        $file = $request->file('file_data');
        if ($file){
            Excel::import(new BulkImport(),$file);
            return redirect()->back()->with('message','Bulk request data uploaded');
        }
        return redirect()->back->with('message','Error occurred in upload');
    }
}
