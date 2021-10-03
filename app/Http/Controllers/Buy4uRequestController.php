<?php

namespace App\Http\Controllers;

use App\Models\HubArea;
use App\Models\Area;
use App\Models\Branch; 
use App\Models\Buy4uProduct;
use App\Models\Buy4uRequest;
use App\Models\Buy4uType;
use App\Models\City;
use App\Models\Pricing;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Buy4uRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $user = Auth::user();
//        if($user->type == 'admin'){ // Temporary fix for admin
        if(!$user || $user->type == 'admin'){
        $buy4u_requests = Buy4uRequest::with('buy4u_type','city','area','branch','pricing','status')->get();
        }else if($user->type == 'delivery_rider'){
            $buy4u_requests = Buy4uRequest::with('buy4u_type','city','area','branch','pricing','status')->where('rider_id', Auth::id())->get();
        }else if($user->type == 'pickup_rider'){
            $buy4u_requests = Buy4uRequest::with('buy4u_type','city','area','branch','pricing','status')->where('rider_id', Auth::id())->get();
        }else if($user->type == 'individual'){
            $buy4u_requests = Buy4uRequest::with('buy4u_type','city','area','branch','pricing','status')->where('customer_id', Auth::id())->get();
        }else if($user->type == 'merchant'){
            $buy4u_requests = Buy4uRequest::with('buy4u_type','city','area','branch','pricing','status')->where('customer_id', Auth::id())->get();
        }
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Buy4u requests loaded!";
        $res->data = $buy4u_requests;
        $statuses = Status::all();
        if ($request->is('dashboard/*')) {
            return view('buy4uRequests.index')
                ->with('statuses',$statuses)
                ->with('buy4u_requests',$res->data);
        }
        return response()->json($res, 200);
    }

    public function create(){
        $buy4u_types = Buy4uType::all();
        $cities = City::all();
        $areas = Area::all();
        $riders = User::where('type','delivery_rider')->orwhere('type','pickup_rider')->get();
        $statuses = Status::all();
        $pricings = Pricing::all();
        return view('buy4uRequests.create')
            ->with('buy4u_types',$buy4u_types)
            ->with('cities',$cities)
            ->with('areas',$areas)
            ->with('riders',$riders)
            ->with('pricings',$pricings)
            ->with('statuses',$statuses);
    }

    public function edit($id){
        $buy4u_request = Buy4uRequest::find($id);
        $buy4u_types = Buy4uType::all();
        $cities = City::all();
        $areas = Area::all();
       $riders = User::where('type','delivery_rider')->orwhere('type','pickup_rider')->get();
        $statuses = Status::all();
        $pricings = Pricing::all();
        return view('buy4uRequests.edit')
            ->with('buy4u_request',$buy4u_request)
            ->with('buy4u_types',$buy4u_types)
            ->with('cities',$cities)
            ->with('areas',$areas)
            ->with('riders',$riders)
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
            'buy4u_type_id' => 'integer|required|exists:buy4u_types,id',
            'city_id' => 'integer|required|exists:cities,id',
            'area_id' => 'integer|required|exists:areas,id',
            'rider_id' => 'integer|required|exists:users,id',
            'branch_id' => 'integer|required',
            'address' => 'string|required',
            'preferred_shop_name' => 'string',
            'preferred_shop_address' => 'string',
            'note'=>'string',
            'status_id'=>'string|required|exists:statuses,id',
            'pricing_id' => 'integer|required|exists:pricings,id'
        ]);
        $validated['customer_id'] = Auth::id() ? Auth::id() : 0;
        /*$validated['branch_id'] = Branch::where('area_id', $validated['area_id'])->first()->id;
        if(!$validated['branch_id']){
            $validated['branch_id'] = Branch::where('city_id', $validated['city_id'])->first()->id;
        }
        if(!$validated['branch_id']){
            $validated['branch_id'] = 0;
        }*/
        $res = new \stdClass();
        try{
            $buy4u_request = Buy4uRequest::create($validated);
            $res->error = false;
            $res->message = "Buy4u request sent!";
            $res->data = [$buy4u_request];

            if ($request->is('dashboard/*')) {
                return redirect('/dashboard/buy4u_request')->with('message',$res->message);
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
            'buy4u_type_id' => 'integer|required|exists:buy4u_types,id',
            'city_id' => 'integer|required|exists:cities,id',
            'area_id' => 'integer|required|exists:areas,id',
            'address' => 'string|required',
            'preferred_shop_name' => 'string',
            'preferred_shop_address' => 'string',
            'note' => 'string',
            'pricing_id' => 'integer|required|exists:pricings,id',
            'products' => 'array'
        ]);
        $validated['status_id'] = Status::where('sequence', 0)->first()->id;
        $validated['customer_id'] = Auth::id() ? Auth::id() : 0;
        /*$branch = Branch::where('area_id', $validated['area_id'])->first();*/
        $branch = HubArea::where('area_id', $validated['sender_area_id'])->first();
        $validated['branch_id'] = $branch ? $branch->hub_id : null;
        if(!$validated['branch_id']){
            $branch = Branch::where('city_id', $validated['city_id'])->first();
            $validated['branch_id'] = $branch ? $branch->id : null;
        }
        if(!$validated['branch_id']){
            $validated['branch_id'] = 0;
        }
        $products = $validated['products'];
        unset($validated['products']);
        $res = new \stdClass();
        try{
            $buy4u_request = Buy4uRequest::create($validated);
            $res->error = false;
            $res->message = "Buy4u request sent!";
            $res->data = [$buy4u_request];
            foreach ($products as $product){
                $product['buy4u_request_id'] = $buy4u_request->id;
                Buy4uProduct::create($product);
            }
            if ($request->is('dashboard/*')) {
                return redirect('/dashboard.buy4u_request')->with('message',$res->message);
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

    public function show(Request $request,$id)
    {
        $rec = Buy4uRequest::with('buy4u_type','city','area','customer','branch','rider','pricing','status','products')->where('id',$id)->first();
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier Request Loaded";
        $res->data = [$rec];
        if ($request->is('dashboard/*')){
            return view('buy4uRequests.show')->with('buy4u_request',$rec);
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
            'buy4u_type_id' => 'integer|required|exists:buy4u_types,id',
            'city_id' => 'integer|required|exists:cities,id',
            'area_id' => 'integer|required|exists:areas,id',
            'rider_id' => 'integer|required|exists:users,id',
            'branch_id' => 'integer|required',
            'address' => 'string|required',
            'note'=>'string',
            'preferred_shop_name' => 'string',
            'preferred_shop_address' => 'string',
            'status_id'=>'string|required|exists:statuses,id',
            'pricing_id' => 'integer|required|exists:pricings,id'
        ]);
        $res = new \stdClass();
        $buy4u_request = Buy4uRequest::findOrFail($id);
        try{
            $buy4u_request->update($validated);
            $res->error = false;
            $res->message = "Buy4u request updated!";
            $res->data = [$buy4u_request];
            if ($request->is('dashboard/*')) {
                return redirect('/dashboard/buy4u_request')->with('message',$res->message);
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
    public function destroy(Request $request,$id)
    {
        $buy4u_request = Buy4uRequest::findOrFail($id);
        $res = new \stdClass();
        try{
            $buy4u_request->delete();
            $res->error = false;
            $res->message = "Buy4u Request Deleted!";
            $res->data = [$buy4u_request];
            if ($request->is('dashboard/*')) {
                return redirect('/dashboard/buy4u_request')->with('message',$res->message);
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
        $buy4u_request = Buy4uRequest::findOrFail($id);
        $buy4u_request->status_id = $validated['status_id'];
        $buy4u_request->save();
        if ($request->is('dashboard/*')){
            return redirect('/dashboard/buy4u_request')->with('message','Status updated');
        }
        return response()->json(BlendxHelpers::generate_response(false, 'Status updated!', []));
    }
}
