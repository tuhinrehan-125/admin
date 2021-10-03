<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Area;
use App\Models\Branch; 
use App\Models\City;
use App\Models\HubArea;
use App\Models\DeliveryMode;
use App\Models\Pricing;
use App\Models\CourierType;
use App\Models\CourierRequest;
use Illuminate\Http\Request;  
 
class BranchesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $all = Branch::with('city','area')->get();
        $response = new \stdClass();
        $response->error = false;
        $response->message = "All records loaded!";
        $response->data = $all;
        if ($request->is('dashboard/*')){
            return view('branches.index')->with('branches',$all);
        }
        return response()->json($response, 200);
    }

    public function create(){
        $areas = Area::all();
        $cities = City::all();
        return view('branches.create')
            ->with('areas',$areas)
            ->with('cities',$cities);
    }

    public function edit($id){
        $branch = Branch::find($id);
        $areas = Area::all();
        $cities = City::all();
        return view('branches.edit')
            ->with('branch',$branch)
            ->with('areas',$areas)
            ->with('cities',$cities);
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
            'name' => 'string|required',
            'email' => 'email|required',
            'phone' => 'string|required',
            'address' => 'string|required',
            'city_id' => 'integer|required|exists:cities,id',
            'area_id' => 'integer|required|exists:areas,id',
            'supervisior_id' => 'integer|required',
            'is_agent' => 'boolean'
        ]);
        $res = new \stdClass();
        try{
            $branch = Branch::create($validated);
            $res->error = false;
            $res->message = "Branch created!";
            $res->data = $branch;
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/branch')->with('message',$res->message);
            }
            return response()->json($res, 201);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            $res->data = $error;
            return response()->json($res, 500);
        }
    }

    public function stores(Request $request)
    {
        $validated = $request->validate([
            'name' => 'string|required',
            'email' => 'email|required',
            'phone' => 'string|required',
            'address' => 'string|required',
            'city_id' => 'integer|required|exists:cities,id',
            'area_id' => 'required',
            'supervisior_id' => 'integer|required',
            'is_agent' => 'boolean'
        ]);
       $hubs = $request->except('_token');
       $hubs['area_id'] = implode(',', $request->area_id);
       $hub = Branch::create($hubs);
       $hub_id['hub_id'] = $hub->id;
       User::where('id',$request->supervisior_id)->update($hub_id);
       if (count($request->area_id) > 0) {
           foreach ($request->area_id as $area_id) {
                $hubes['area_id'] = $area_id;
                $hubes['hub_id'] = $hub->id;
                HubArea::create($hubes);
           }
       }
       session()->flash('message','Hub Added Successfully');
        return redirect(route('dashboard.branch'));
       
    }

    public function updates(Request $request, $id){
        $validated = $request->validate([
            'name' => 'string|required',
            'email' => 'email|required',
            'phone' => 'string|required',
            'address' => 'string|required',
            'city_id' => 'integer|required|exists:cities,id',
            'supervisior_id' => 'integer|required',
            'is_agent' => 'boolean'
        ]);
       $hubs = $request->except('_token');
        if (isset($request->area_id)) {
            $hubs['area_id'] = implode(',', $request->area_id);
        }
       $hub = Branch::where('id',$id)->update($hubs);
       $hub_id['hub_id'] = $id;
       User::where('id',$request->supervisior_id)->update($hub_id);
        if (isset($request->area_id)) {
           if (count($request->area_id) > 0) {
                $hubareas = HubArea::where('hub_id',$id)->get();
                foreach ($hubareas as $hubarea) {
                    $hubarea->delete();
                }
               foreach ($request->area_id as $area_id) {
                    $hubes['area_id'] = $area_id;
                    $hubes['hub_id'] = $id;
                    HubArea::create($hubes);
               }
           }
       }
       session()->flash('message','Hub Update Successfully');
        return redirect(route('dashboard.branch'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'string|required',
            'email' => 'email|required',
            'phone' => 'string|required',
            'address' => 'string|required',
            'city_id' => 'integer|required|exists:cities,id',
            'area_id' => 'integer|required|exists:areas,id',
            'is_agent' => 'boolean'
        ]);
        $res = new \stdClass();
        try{
            $branch = Branch::findOrFail($id)->update($validated);
            $res->error = false;
            $res->message = "Branch updated!";
            $res->data = $branch;
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/branch')->with('message',$res->message);
            }
            return response()->json($res, 201);
        }catch (\Exception $error){
            $res->error = true;
            $res->message = $error->getMessage();
            $res->data = $error;
            return response()->json($res, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        $hubs =HubArea::where('hub_id',$id)->get();
        foreach ($hubs as $hub) {
            $hub->delete();
        }
        $branch = Branch::find($id);
        $branch->delete();
        return redirect('/dashboard/branch')->with('message','Hub deleted');
    }

    public function getarea(Request $request)
    {
        $area = Area::where('city_id',$request->city_id)->get();
        return $area;
    }
    public function gethub(Request $request)
    {
        $area = HubArea::where('area_id',$request->area_id)->first()->hub_id;
        $hub = Branch::where('id',$area)->get();
        return $hub;
    }
    public function gethubbycity(Request $request)
    {
        $city = Branch::where('city_id',$request->cities)->get();
        return $city;
    }
    public function getareabyhub(Request $request)
    {
        $hubs = \DB::table('areas')->join('hub_areas','hub_areas.area_id','areas.id')->where('hub_areas.hub_id',$request->rider_hub)->select('areas.id','areas.name')->get();
        return $hubs;
    }
    public function getsenderarea(Request $request)
    {
        $area = Area::where('city_id',$request->sender_city_id)->get();
        return $area;
    }
    public function getreceiverarea(Request $request)
    {
        $area = Area::where('city_id',$request->receiver_city_id)->get();
        return $area; 
    }

    public function getareahub(Request $request)
    {
        $area = HubArea::where('area_id',$request->sender_area_id)->first()->hub_id;
        $hub = Branch::where('id',$area)->get();
        return $hub;
    }
    
    public function getdelivermode(Request $request){
        $sender_city = City::find($request->sender_city_id);
        $receiver_city = City::find($request->receiver_city_id);
        if (($sender_city->name == "Dhaka") && ($receiver_city->name == "Dhaka")){
            return CourierType::where('id',3)->get();
        } elseif (($sender_city->name == "Chittagong") && ($receiver_city->name == "Chittagong")) {
            return CourierType::where('id',8)->get();
        } elseif (($sender_city->name == "Chittagong") && ($receiver_city->name == "Dhaka") || ($sender_city->name == "Dhaka") && ($receiver_city->name == "Chittagong")) {
            return CourierType::where('id',5)->get();
        }elseif ( (($sender_city->name == "Dhaka") && (($receiver_city->name == "Ashulia") || ($receiver_city->name == "Gazipur")|| ($receiver_city->name == "Savar") || ($receiver_city->name == "Narayanganj") || ($receiver_city->name == "Keraniganj"))) ||  ( (($sender_city->name == "Ashulia") || ($sender_city->name == "Gazipur")|| ($sender_city->name == "Savar") || ($sender_city->name == "Narayanganj") || ($sender_city->name == "Keraniganj")) && ($receiver_city->name == "Dhaka") ) ) {
            return CourierType::where('id',9)->get();
        } else {
            return CourierType::where('id',13)->get();
        }

    }

    public function getdeliverytype(Request $request){
        $type = DeliveryMode::where('courier_type_id',$request->courier_type_id)->get();
       return $type;
        
    }

    public function getweight(Request $request){
        $weight = Pricing::where('courier_type_id', $request->courier_type_id)
            ->where('delivery_mode_id', $request->delivery_mode_id)->get();
        return $weight;
    }

    public function getpricebyweight(Request $request){ 
        $pricing = Pricing::where('id',$request->approximate_weight)->get();    
        return $pricing;
    }
    
    public function accounts(Request $request){
        $branch = new Branch;
        if($request->has('hubs') && $request->hubs != null){
            if ($request->hubs == '3') {
                $branch = $branch->where('city_id','4');
            }else{
                $branch = $branch->where('city_id','!=','4')->orwhere('city_id',null);
            }
            
        }
        $all = $branch->orderBy('id','desc')->get();
        $response = new \stdClass();
        $response->error = false; 
        $response->message = "All records loaded!";
        $response->data = $all; 
        return view('branches.accounts')->with('branches',$all);

    }
    
    public function paymentReceive(){
        $all = Branch::with('city','area')->get();
        $response = new \stdClass();
        $response->error = false; 
        $response->message = "All records loaded!";
        $response->data = $all;
        return view('branches.paymentReceive')->with('branches',$all);

    }

    public function paymentHubList(Request $request, $id){
        $CourierRequest = new CourierRequest();
        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('id',$request->courierid)->where('status_id','18');
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid)->where('status_id','18');
        }
        $CourierRequest = $CourierRequest->where('status_id','18')->where('hub_payment','yes')
                        ->where(function($query)use($id){
                            $query->where(function($query)use($id){
                                 $query->where('delivery_hub',$id);
                             })
                            ->orWhere(function($query)use($id){
                                 $query->where('branch_id',$id)->where('paid_by', 'sender');
                             });
                         })

                    ->orderBy('id','desc')->paginate(100);

        if (isset($request->courierid) || $request->trackingid || $request->merchantname) {
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $render['merchantname'] = $request->merchantname;
            $CourierRequest = $CourierRequest->appends($render);
        }

        $data['courier_requests'] = $CourierRequest;
        $data['id'] = $id;
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $data['courier_requests'];
        $data['hub_payment'] = "1";
        return view('branches.hub_payment',$data); 
    }
    
}
