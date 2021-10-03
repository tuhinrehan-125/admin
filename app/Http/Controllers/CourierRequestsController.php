<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\HubArea;

use App\Models\Branch;
use App\Models\HubTransfer;
use App\Models\User;
use App\Models\City;
use App\Models\CourierRequest;
use App\Models\CourierType; 
use App\Models\DeliveryMode;
use App\Models\PackagingType;
use App\Models\Pricing;
use App\Models\Status; 
use App\Models\CourierRequestLog;
use App\Models\Invoice;   
use App\Models\InvoiceList;  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use Carbon\Carbon;
use PDF;
use Mail;
use App\Models\AgentPaymentLog;

class CourierRequestsController extends Controller
{
 
    public function index(Request $request)
    { 
        $user = Auth::user();
        $CourierRequest = new CourierRequest();

        if(($request->has('pickupdate') && $request->pickupdate != null) && ($request->has('merchantname') && $request->merchantname != null)){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate)->where('customer_id',$request->merchantname);
        } 
        if($request->has('courierid') && $request->courierid != null){
            $arr = explode(',',$request->courierid);
            $CourierRequest = $CourierRequest->whereIn('id', $arr);
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid);
        }
        if($request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('customer_id',$request->merchantname);
        }
        if($request->has('receiverPhone') && $request->receiverPhone != null){
            $CourierRequest = $CourierRequest->where('receiver_phone',$request->receiverPhone);
        }
        if($request->has('pickupdate') && $request->pickupdate != null){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate);
        }
        $CourierRequest = $CourierRequest->orderBy('id','desc')->paginate(100);
        if (isset($request->pickupdate) || $request->courierid || $request->trackingid || $request->merchantname) {
            $render['pickupdate'] = $request->pickupdate;
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $render['merchantname'] = $request->merchantname;
            $CourierRequest = $CourierRequest->appends($render);
        }
        $data['courier_requests'] = $CourierRequest;

        
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        
        $data['all'] = "1";
        /*$res->data = $courier_requests;*/
        $data['statuses'] = Status::all();
        if ($request->is('dashboard/*')) {
            return view('courierRequests.index',$data);
        }else{
            if($user->type == 'delivery_rider'){
            $courier_requests = CourierRequest::with('delivery_mode','packaging_type','receiver_area','sender_area','branch',
                'receiver_city','sender_city','courier_type','status','pricing','customer')->where('rider_id', Auth::id())->orderBy('id','desc')->get();
            }else if($user->type == 'pickup_rider'){
            $courier_requests = CourierRequest::with('delivery_mode','packaging_type','receiver_area','sender_area','branch',
                'receiver_city','sender_city','courier_type','status','pricing','customer')->where('rider_id', Auth::id())->orderBy('id','desc')->get();
            }else if($user->type == 'individual'){
            $courier_requests = CourierRequest::with('delivery_mode','packaging_type','receiver_area','sender_area','branch',
                'receiver_city','sender_city','courier_type','status','pricing','customer')->where('customer_id', Auth::id())->orderBy('id','desc')->get();
            }else if($user->type == 'merchant'){
            $courier_requests = CourierRequest::with('delivery_mode','packaging_type','receiver_area','sender_area','branch',
                'receiver_city','sender_city','courier_type','status','pricing','customer')->where('customer_id', Auth::id())->orderBy('id','desc')->get();
            }
            $res->data = $courier_requests;
            return response()->json($res, 200);
        }
    }

    public function create(){
        $courier_types = CourierType::all();
        $cities = City::all();
        $areas = Area::all();
        $package_types = PackagingType::all();
        $delivery_modes = DeliveryMode::all();
        $pricings = Pricing::all();
        $statuses = Status::all();
        return view('courierRequests.create')
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
        $riders = User::where('type','delivery_rider')->orwhere('type','pickup_rider')->get();
        $pricings = Pricing::all();
        $statuses = Status::all();
        return view('courierRequests.edit')
            ->with('courier_request',$courier_request)
            ->with('courier_types',$courier_types)
            ->with('cities',$cities)
            ->with('areas',$areas)
            ->with('package_types',$package_types)
            ->with('delivery_modes',$delivery_modes)
            ->with('pricings',$pricings)
            ->with('statuses',$statuses)
            ->with('riders',$riders);
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
            'customer_id' => 'integer|required',
            'packaging_type_id' => 'integer|required|exists:packaging_types,id',
            'delivery_mode_id' => 'integer|required|exists:delivery_modes,id',
            'sender_address' => 'string|required',
            'receiver_address' => 'string|required',
            'receiver_name' => 'string|required',
            'sender_name' => 'string',
            'receiver_phone' => 'string|required',
            'sender_phone' => 'string',
            'fragile' => 'boolean|required',
            'paid_by' => 'string|in:receiver,sender,merged_with_cod',
            'cash_on_delivery' => 'boolean',
            'cash_on_delivery_amount' => 'required_if:cash_on_delivery,1|required_if:paid_by,merged_with_cod',
            'approximate_weight' => 'numeric|required',
            'pricing_id' => 'integer|required|exists:pricings,id'
        ]); 
        $status = Status::where('sequence', 0)->first();
        
        $weight = Pricing::where('id',$validated['approximate_weight'])->first();
        $approximate_weight = $weight->max_weight;

        $validated['approximate_weight'] = $approximate_weight;
        $validated['actual_weight'] = $approximate_weight;
        
        $validated['status_id'] = $status ? $status->id : 0;
        if (!empty($request->branch_id)) {
            $validated['branch_id'] = $request->branch_id;
        }else{
            $branch = HubArea::where('area_id', $validated['sender_area_id'])->first();
            $validated['branch_id'] = $branch ? $branch->hub_id : 21;
        }
         $deliveryhub = HubArea::where('area_id', $validated['receiver_area_id'])->first();
        $validated['delivery_hub'] = $deliveryhub ? $deliveryhub->hub_id : 21;
        
        /*$validated['customer_id'] = Auth::id() ? Auth::id() : 0;*/
        $validated['rider_id'] = '0';
        $validated['note'] = $request->note;
        /*$branch = HubArea::where('area_id', $validated['sender_area_id'])->first();*/
        
        /*$validated['branch_id'] = $branch ? $branch->hub_id : null;
        if(!$validated['branch_id']){
            $branch = Branch::where('city_id', $validated['sender_city_id'])->first();
            $validated['branch_id'] = $branch ? $branch->id : null;
        }
        if(!$validated['branch_id']){
            $validated['branch_id'] = 0;
        } */
        $res = new \stdClass();
        try{
            $courier_request = CourierRequest::create($validated);
            $res->error = false;
            $res->message = "Courier request sent!";
            $res->data = [$courier_request];

            $currie['tracking_id'] = time().''.$courier_request->id;
            CourierRequest::where('id', $courier_request->id)->update($currie);

            $courier['courier_id'] = $courier_request->id;
            $courier['name'] = $status->name;
            $courier['sequence'] = $status->sequence;
            $courier['status_id'] = $status->id;
            CourierRequestLog::create($courier);

            if ($request->is('dashboard/*')) {
                return redirect('/dashboard/courier_request')->with('message','Courier request created');
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
            'sender_address' => 'string|required',
            'receiver_address' => 'string|required',
            'receiver_name' => 'string|required',
            'receiver_phone' => 'string|required',
            'fragile' => 'boolean|required',
            'paid_by' => 'string|in:receiver,sender,merged_with_cod',
            'cash_on_delivery' => 'boolean',
            'cash_on_delivery_amount' => 'required_if:cash_on_delivery,1|required_if:paid_by,merged_with_cod',
            'approximate_weight' => 'numeric|required',
            'pricing_id' => 'integer|required|exists:pricings,id'
        ]);
        $status = Status::where('sequence', 0)->first();
        $validated['status_id'] = $status ? $status->id : 0;
        $validated['actual_weight'] = $validated['approximate_weight'];
        $validated['customer_id'] = Auth::id() ? Auth::id() : 0;
        $validated['rider_id'] =  "0";
        $validated['note'] = $request->note;
        /*$branch = Branch::where('area_id', $validated['sender_area_id'])->first();*/
        $branch = HubArea::where('area_id', $validated['sender_area_id'])->first();
        $validated['branch_id'] = $branch ? $branch->hub_id : 21;
        if(!$validated['branch_id']){
            $branch = Branch::where('city_id', $validated['sender_city_id'])->first();
            $validated['branch_id'] = $branch ? $branch->id : 21;
        }
        if(!$validated['branch_id']){
            $validated['branch_id'] = 0;
        }
        
         $deliveryhub = HubArea::where('area_id', $validated['receiver_area_id'])->first();
        $validated['delivery_hub'] = $deliveryhub ? $deliveryhub->hub_id : 21;
        
        $res = new \stdClass();
        try{
            $courier_request = CourierRequest::create($validated);
            
            if (Auth::user()->free_req > 0) {
                if ($request->delivery_mode_id == "35" || $request->delivery_mode_id == "36") {
                    $free = Auth::user()->free_req;
                    $frees['free_req'] = $free - 1;
                    User::where('id',Auth::user()->id)->update($frees);
                }
            }
            
            $res->error = false;
            $res->message = "Courier request sent!";
            $res->data = [$courier_request];

            $currie['tracking_id'] = time().''.$courier_request->id.'a';
            CourierRequest::where('id', $courier_request->id)->update($currie);

            $courier['courier_id'] = $courier_request->id;
            $courier['name'] = $status->name;
            $courier['sequence'] = $status->sequence;
            $courier['status_id'] = $status->id;
            $courier['user_id'] = Auth::user()->id;
            CourierRequestLog::create($courier);

            if ($request->is('dashboard/*')) {
                return redirect('/dashboard/courier_request')->with('message','Courier request created');
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
        $res->message = "Courier Request Loaded";
        $res->data = [$rec];

        if ($request->is('dashboard/*')){
            return view('courierRequests.show')->with('courier_request',$rec);
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
            'customer_id' => 'integer|required',

            'packaging_type_id' => 'integer|exists:packaging_types,id',
            'delivery_mode_id' => 'integer|exists:delivery_modes,id',
            'sender_address' => 'string',
            'receiver_address' => 'string',
            'receiver_name' => 'string',
            'status_id' => 'integer|exists:statuses,id',

            'sender_name' => 'string',
            'sender_phone' => 'string',
            'receiver_phone' => 'string',
            'fragile' => 'boolean',
            'paid_by' => 'string|in:receiver,sender,merged_with_cod',
            'cash_on_delivery' => 'boolean',
            'cash_on_delivery_amount' => 'required_if:cash_on_delivery,1|required_if:paid_by,merged_with_cod',

            'approximate_weight' => 'numeric|required',
            'pricing_id' => 'integer|exists:pricings,id'
        ]);

        $validated['actual_weight'] = $validated['approximate_weight'];
        $validated['rider_id'] = $request->rider_id;
        if (!empty($request->branch_id)) {
            $validated['branch_id'] = $request->branch_id;
        }else{
            $branch = HubArea::where('area_id', $validated['sender_area_id'])->first();
            $validated['branch_id'] = $branch ? $branch->hub_id : 21;
        }
        
        $deliveryhub = HubArea::where('area_id', $validated['receiver_area_id'])->first();
        $validated['delivery_hub'] = $deliveryhub ? $deliveryhub->hub_id : 21;
        
        $res = new \stdClass();
        $courier_request = CourierRequest::findOrFail($id);
        try{
            $courier_request->update($validated);
            $res->error = false;
            $res->message = "Courier request updated!";
            $res->data = [$courier_request];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/courier_request')->with('message',$res->message);
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
            $logs = CourierRequestLog::where('courier_id',$id)->get();
            foreach ($logs as $log) {
                $log->delete();
            }
            $transfers = HubTransfer::where('courier_id',$id)->get();
            foreach ($transfers as $transfer) {
                $transfer->delete();
            }
            $res->error = false;
            $res->message = "Courier Request Deleted!";
            $res->data = [$courier_request];
            if ($request->is('dashboard/*')){
                return redirect('/dashboard/courier_request')->with('message',$res->message);
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
            /*'sender_city_id' => 'integer|required|exists:cities,id',
            'receiver_city_id' => 'integer|required|exists:cities,id',*/
            'delivery_mode_id' => 'integer|required|exists:delivery_modes,id',
            'approximate_weight' => 'numeric|required|min:0.1',
        ]);
        if (Auth::user()->speical == 1) {
            $pricing = Pricing::where('user_id',Auth::user()->id)->where('min_weight', '<', $validated['approximate_weight'])
            ->where('max_weight', '>=', $validated['approximate_weight'])
            ->where('courier_type_id', $validated['courier_type_id'])
           /* ->where('sender_city_id', $validated['sender_city_id'])
            ->where('receiver_city_id', $validated['receiver_city_id'])*/
            ->where('delivery_mode_id', $validated['delivery_mode_id'])->first();
        }else{
            if (Auth::user()->free_req > 0) {
                if ($validated['delivery_mode_id'] == "35" || $validated['delivery_mode_id'] == "36") {
                    $pricing = Pricing::where('user_id',0)->where('min_weight', '<', $validated['approximate_weight'])
                            ->where('max_weight', '>=', $validated['approximate_weight'])
                            ->where('courier_type_id', '0')
                            ->where('delivery_mode_id', '0')->first();
                }else{
                    $pricing = Pricing::where('user_id',0)->where('min_weight', '<', $validated['approximate_weight'])
                            ->where('max_weight', '>=', $validated['approximate_weight'])
                            ->where('courier_type_id', $validated['courier_type_id'])
                            ->where('delivery_mode_id', $validated['delivery_mode_id'])->first();
                }
            }else{
                $pricing = Pricing::where('user_id',0)->where('min_weight', '<', $validated['approximate_weight'])
                            ->where('max_weight', '>=', $validated['approximate_weight'])
                            ->where('courier_type_id', $validated['courier_type_id'])
                            ->where('delivery_mode_id', $validated['delivery_mode_id'])->first();
            }
        }
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
        

        $stat = Status::find($request->status_id);
        $courier['courier_id'] = $id;
        $courier['name'] = $stat->name;
        $courier['sequence'] = $stat->sequence;;
        $courier['status_id'] = $request->status_id;
        CourierRequestLog::create($courier);

        if ($request->is('dashboard/*')){
            return redirect('/dashboard/courier_request')->with('message','Courier request status updated');
        }
        return response()->json(BlendxHelpers::generate_response(false, 'Status updated!', []));
    }
    
        public function status_change(Request $request){
        $validated = $request->validate([
            'status_id' => 'integer|required|exists:statuses,id',
            'courier_id.*' => 'integer|required'
        ]);
        foreach ($request->courier_id as $courier_id) {
            $status = Status::findOrFail($validated['status_id']);

            if(!$status){
                return BlendxHelpers::generate_response(true, "Status with id: ".$validated['status_id']." not found", []);
            }

            $courier_request = CourierRequest::findOrFail($courier_id);
            $data['status_id'] = $request->status_id;
            if ($request->status_id == "18"){
                $data['delivery_date'] = date('Y-m-d H:i:s');
            }
            CourierRequest::where('id',$courier_id)->update($data);
             
            $stat = Status::find($request->status_id);
            $courier['courier_id'] = $courier_id;
            $courier['name'] = $stat->name;
            $courier['sequence'] = $stat->sequence;;
            $courier['status_id'] = $request->status_id;
            $courier['note'] = $request->note;
            $courier['user_id'] = Auth::user()->id;
            CourierRequestLog::create($courier);

            if ($request->status_id == "17") {
                if (!empty($request->note)) {
                    $courier_request = CourierRequest::findOrFail($courier_id);
                    $text = "Tracking ID ".$courier_request->tracking_id.':'.$request->note;
                    $to = User::find($courier_request->customer_id)->phone;
                    $this->sms($to, $text);
                }
                
            }

            if ($request->status_id == "16") {
                $courier_request = CourierRequest::findOrFail($courier_id);
                $text = "Dear ". $courier_request->receiver_name.",Your parcel is on the way to Deliver";
                $to = $courier_request->receiver_phone;
                $this->sms($to, $text);
            }
        }
        return response()->json(BlendxHelpers::generate_response(false, 'Status updated!', []));
    }

    public function tracker($id){
        $cur_id = CourierRequest::where('tracking_id',$id)->first();
        $courier = CourierRequestLog::where('courier_id',$cur_id->id)->get();
        $response = [
            'msg' => 'Tracking requests loaded',
            'parcel_log' => $courier
        ];
        return response()->json($response, 200);
    }

    public function cod_update_status(Request $request, $id){
        $courier_request = CourierRequest::findOrFail($id);
        $courier_request->cod_payment_status = $request->cod_payment_status;
        $courier_request->save();
        if ($request->is('dashboard/*')){
            return redirect('/dashboard/courier_request')->with('message','Courier request status updated');
        }
    }

    public function update_otp_status(Request $request){
        $validated = $request->validate([
            'phone_number' => 'required'
        ]);
        $ver_code = rand(100000, 999999);
        $text = "You Verfication Code is ".$ver_code;
        $to = $request->phone_number;
        $this->sms($to,$text);
        return response()->json([
            'success' => 'yes',
            'verfication' => $ver_code
        ], 201);
    }

    public function verfication(Request $request,$id){
        $courier_request = CourierRequest::findOrFail($id);
        $user = User::where('id',$courier_request->customer_id)->first();
        if ($user->verification_code == $request->verification_code) {
             return response()->json([
                'success' => 'yes',
                'message' => 'Your number verified successfully'
            ], 201);
        }
        else{
             return response()->json([
                'success' => 'no',
                'message' => 'Invalid verification Code'
            ], 201);
        }
    }

    public function sms($to, $text){
        $msisdn = $to;
        $messageBody=$text;
        $csmsId = "2934fesa343";
        $params = [
        "api_token" => "Holister-f36e6c7d-af4c-434c-895e-04666f9a4768",
        "sid" => 'HOLISTERMASKAPI',
        "msisdn" => $msisdn,
        "sms" => $messageBody,
        "csms_id" => $csmsId
        ];
        $url = "https://smsplus.sslwireless.com/api/v3/send-sms";
        $params = json_encode($params);

        return $this->callApi($url, $params);
    }

    function callApi($url, $params)
    {
        $ch = curl_init(); // Initialize cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($params),
            'accept:application/json'
        ));

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }
    
    public function bulk_status(Request $request){
        if (!empty($request->ids)) {
            foreach ($request->ids as $ide) {
                if (!empty($request->cod_payment)) {
                    $courier_request = CourierRequest::findOrFail($ide);
                    $data['cod_payment_status'] = $request->cod_payment;
                    $data['cod_payment_status_date'] = date('Y-m-d H:i:s'); 
                    $data['preferred_method'] = (!empty($courier_request->customer->preferred_method) ? $courier_request->customer->preferred_method : 'Not Available');
                    if($courier_request->customer->preferred_method == "bkash"){
                        $preferred_method_number = $courier_request->customer->bkash_no;
                    }elseif($courier_request->customer->preferred_method == "nagad"){
                        $preferred_method_number = $courier_request->customer->nagad_no; 
                    }elseif($courier_request->customer->preferred_method == "rocket"){
                        $preferred_method_number = $courier_request->customer->rocket_no;
                    }elseif($courier_request->customer->preferred_method == "bank"){
                        $preferred_method_number = $courier_request->customer->bank_ac_no;
                    }else{
                        $preferred_method_number = "Not Available";
                    }
                    $data['preferred_method_number'] = $preferred_method_number;
                    CourierRequest::where('id',$ide)->update($data);
                    $this->pushnotification($courier_request->tracking_id,$courier_request->customer_id);
                }elseif (!empty($request->hub_payment)) {
                    $data['hub_payment'] = $request->hub_payment;
                    $data['hub_payment_date'] = date('Y-m-d H:i:s');
                    CourierRequest::where('id',$ide)->update($data);

                }elseif(!empty($request->delivery_hub)){
                    $hubs['hub_status'] = '1';
                    $hubs['delivery_hub'] = $request->delivery_hub;
                    CourierRequest::where('id',$ide)->update($hubs);

                    $transfer['courier_id'] = $ide;
                    $transfer['hub_id'] = $request->delivery_hub;
                    $transfer['hub_status'] = '1';
                    HubTransfer::create($transfer);
                    
                }else{
                    $status = Status::findOrFail($request->status_ids);
                    $data['status_id'] = $request->status_ids;
                    if ($request->status_ids == "18"){
                        $data['delivery_date'] = date('Y-m-d H:i:s');
                    }
                    CourierRequest::where('id',$ide)->update($data);
                    
                    $stat = Status::find($request->status_ids);
                    $courier['courier_id'] = $ide;
                    $courier['name'] = $stat->name;
                    $courier['sequence'] = $stat->sequence;;
                    $courier['status_id'] = $request->status_ids;
                    $courier['note'] = $request->note;
                    $courier['user_id'] = Auth::user()->id;
                    CourierRequestLog::create($courier);

                    if ($request->status_ids == "13") {
                        $courieres = CourierRequest::find($ide);
                        $picks = User::find($courieres->pickup_rider);

                        $rider['courier_id'] = $ide;
                        $rider['rider_id'] = $courieres->pickup_rider;
                        $rider['type'] = 'pickup_rider';
                        $rider['amount'] = !empty($picks->pickup_rider_commission)?$picks->pickup_rider_commission:'0';
                        $rider['addedby'] = Auth::user()->id;
                        \App\Models\RiderCommission::create($rider);

                        
                        $branch = Branch::find($courieres->branch_id);
                        if ($branch->is_agent == '1') {
                            $agent_picks = User::where('hub_id',$courieres->branch_id)->first();
                            $agent['courier_id'] = $ide;
                            $agent['agent_id'] = $agent_picks->id;
                            $agent['type'] = 'pickup_agent';
                            $per = !empty($agent_picks->pickup_agent_commission)?$agent_picks->pickup_agent_commission:'0';
                            $agent['percentage'] = $per;

                            $price = Pricing::find($courieres->pricing_id)->price;
                            $agent['charge'] = $price;
                            $amounts = ($price*$per)/100;
                            $agent['amount'] = $amounts;
                            $agent['addedby'] = Auth::user()->id;
                            \App\Models\AgentCommission::create($agent);
                        }else{
                            $agent_picks = User::where('hub_id',$courieres->branch_id)->first();
                            $amounts = '0';
                        }

                        $datas['pickup_rider_commission'] = !empty($picks->pickup_rider_commission)?$picks->pickup_rider_commission:'0';
                        $datas['pickup_rider_commission_added'] = Auth::user()->id;
                        $datas['pickup_agent_commission'] = $amounts;
                        $datas['pickup_agent_commission_added'] = Auth::user()->id;
                        $datas['pickup_agent_id'] = $agent_picks->id;
                        $datas['pickup_date'] = date('Y-m-d H:i:s');
                        CourierRequest::where('id',$ide)->update($datas);
                        
                    }

                    if ($request->status_ids == "17") {
                        if (!empty($request->note)) {
                            $courier_request = CourierRequest::findOrFail($ide);
                            $text = "Tracking ID ".$courier_request->tracking_id.':'.$request->note;
                            $to = User::find($courier_request->customer_id)->phone;
                            $this->sms($to, $text);
                        }
                        
                    }

                    if ($request->status_ids == "16") {
                        $courier_request = CourierRequest::findOrFail($ide);
                        $text = "Dear ". $courier_request->receiver_name.",Your parcel is on the way to Deliver";
                        $to = $courier_request->receiver_phone;
                        $this->sms($to, $text);

                        $courieres = CourierRequest::find($ide);
                        $picks = User::find($courieres->delivery_rider);
                        $rider['courier_id'] = $ide;
                        $rider['rider_id'] = $courieres->delivery_rider;
                        $rider['type'] = 'delivery_rider';
                        $rider['amount'] = !empty($picks->delivery_rider_commission)?$picks->delivery_rider_commission:'0';
                        $rider['addedby'] = Auth::user()->id;
                        \App\Models\RiderCommission::create($rider);

                        
                        $branch = Branch::find($courieres->branch_id);
                        if ($branch->is_agent == '1') {
                            $agent_picks = User::where('hub_id',$courieres->branch_id)->first();
                            $agent['courier_id'] = $ide;
                            $agent['agent_id'] = $agent_picks->id;
                            $agent['type'] = 'delivery_agent';
                            $per = !empty($agent_picks->delivery_agent_commission)?$agent_picks->delivery_agent_commission:'0';
                            $agent['percentage'] = $per;

                            $price = Pricing::find($courieres->pricing_id)->price;
                            $agent['charge'] = $price;
                            $amounts = ($price*$per)/100;
                            $agent['amount'] = $amounts;
                            $agent['addedby'] = Auth::user()->id;
                            \App\Models\AgentCommission::create($agent);
                        }else{
                            $agent_picks = User::where('hub_id',$courieres->branch_id)->first();
                            $amounts = '0';
                        }

                        $datas['delivery_rider_commission'] = !empty($picks->delivery_rider_commission)?$picks->delivery_rider_commission:'0';
                        $datas['delivery_rider_commission_added'] = Auth::user()->id;
                        $datas['delivery_agent_commission'] = $amounts;
                        $datas['delivery_agent_commission_added'] = Auth::user()->id;
                        $datas['delivery_agent_id'] = $agent_picks->id;
                        $datas['assign_date'] = date('Y-m-d H:i:s');
                        CourierRequest::where('id',$ide)->update($datas);
                    }
                }
            }
            session()->flash('message','Update Successfully');
            return redirect()->back();
        } else {
            session()->flash('message','Not selected any checkbox');
            return redirect()->back();
        }
    }

    
    public function printer($id){
        $courier = CourierRequest::findOrFail($id);
        return view('courierRequests.print',compact('courier'));
    }
    
    public function hub_transfer($id){
        $data['courier'] = CourierRequest::find($id);
        $data['hub_transfers'] = HubTransfer::where('courier_id',$id)->get();
        $data['hubs'] = Branch::get();
        return view('courierRequests.hubtransfer',$data);
    }

    public function hub_transfer_store(Request $request, $id){
        $hubs['hub_status'] = $request->hub_status;
        if ($request->hub_status == "1") {
            $hubs['delivery_hub'] = $request->hub_id;
        }
        if ($request->hub_status == "2") {
            $hubs['transit_hub'] = $request->hub_id;
        }
        CourierRequest::where('id',$id)->update($hubs);

        $transfer['courier_id'] = $id;
        $transfer['hub_id'] = $request->hub_id;
        $transfer['hub_status'] = $request->hub_status;
        HubTransfer::create($transfer);
        session()->flash('message','Successfully Transfer');
        return redirect()->back();
    }
    
    public function pickup(){
        $data['courier_requests'] = CourierRequest::where('hub_status','0')->where('status_id','!=','18')->with('delivery_mode','packaging_type','receiver_area','sender_area','branch',
            'receiver_city','sender_city','courier_type','status','pricing','customer')->orderBy('id','desc')->get();
        $data['statuses'] = Status::all();
        return view('courierRequests.pickup',$data);
    }

    public function transit(){
        $data['courier_requests'] = CourierRequest::where('hub_status','2')->where('status_id','!=','18')->with('delivery_mode','packaging_type','receiver_area','sender_area','branch',
            'receiver_city','sender_city','courier_type','status','pricing','customer')->orderBy('id','desc')->get();
        $data['statuses'] = Status::all();
        return view('courierRequests.transit',$data);
    }

    public function delivery(){
        $data['courier_requests'] = CourierRequest::where('hub_status','1')->where('status_id','!=','18')->with('delivery_mode','packaging_type','receiver_area','sender_area','branch',
            'receiver_city','sender_city','courier_type','status','pricing','customer')->orderBy('id','desc')->get();
        $data['statuses'] = Status::all();
        return view('courierRequests.delivery',$data);
    }
    
    public function cod_payment_status(Request $request){
        $courier_request = CourierRequest::findOrFail($request->courier_id);
        $data['cod_payment_status'] = $request->status;
        $data['cod_payment_status_date'] = date('Y-m-d H:i:s');
        $data['preferred_method'] = (!empty($courier_request->customer->preferred_method) ? $courier_request->customer->preferred_method : 'Not Available');
        if($courier_request->customer->preferred_method == "bkash"){
            $preferred_method_number = $courier_request->customer->bkash_no;
        }elseif($courier_request->customer->preferred_method == "nagad"){
            $preferred_method_number = $courier_request->customer->nagad_no; 
        }elseif($courier_request->customer->preferred_method == "rocket"){
            $preferred_method_number = $courier_request->customer->rocket_no;
        }elseif($courier_request->customer->preferred_method == "bank"){
            $preferred_method_number = $courier_request->customer->bank_ac_no;
        }else{
            $preferred_method_number = "Not Available";
        }
        $data['preferred_method_number'] = $preferred_method_number;
        CourierRequest::where('id',$request->courier_id)->update($data);
        
        $this->pushnotification($courier_request->tracking_id,$courier_request->customer_id);
        
        return response()->json(['success'=>'Status change successfully.']);
    }
    
    public function remaining(Request $request){
        
        $CourierRequest = new CourierRequest();

        if(($request->has('pickupdate') && $request->pickupdate != null) && ($request->has('merchantname') && $request->merchantname != null)){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate)->where('customer_id',$request->merchantname)->where('status_id','!=','12')->where('status_id','!=','18')->where('status_id','!=','20')->where('status_id','!=','17');
        } 
        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('id',$request->courierid)->where('status_id','!=','12')->where('status_id','!=','18')->where('status_id','!=','20')->where('status_id','!=','17');
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid)->where('status_id','!=','12')->where('status_id','!=','18')->where('status_id','!=','20')->where('status_id','!=','17');
        }
        if($request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('customer_id',$request->merchantname)->where('status_id','!=','12')->where('status_id','!=','18')->where('status_id','!=','20')->where('status_id','!=','17');
        }
        if($request->has('pickupdate') && $request->pickupdate != null){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate)->where('status_id','!=','12')->where('status_id','!=','18')->where('status_id','!=','20')->where('status_id','!=','17');
        }
        $CourierRequest = $CourierRequest->where('status_id','!=','12')->where('status_id','!=','18')->where('status_id','!=','20')->where('status_id','!=','17')->orderBy('id','desc')->paginate(100);
        
        if (isset($request->pickupdate) || $request->courierid || $request->trackingid || $request->merchantname) {
            $render['pickupdate'] = $request->pickupdate;
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $render['merchantname'] = $request->merchantname;
            $CourierRequest = $CourierRequest->appends($render);
        }
        $data['courier_requests'] = $CourierRequest;

        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $data['courier_requests'];
        $data['statuses'] = Status::all();
        $data['remaining'] = "1";
        return view('courierRequests.index',$data);
    }

    public function delivered(Request $request){
        $CourierRequest = new CourierRequest();

        if(($request->has('pickupdate') && $request->pickupdate != null) && ($request->has('merchantname') && $request->merchantname != null)){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate)->where('customer_id',$request->merchantname)->where('status_id','18');
        } 
        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('id',$request->courierid)->where('status_id','18');
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid)->where('status_id','18');
        }
        if($request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('customer_id',$request->merchantname)->where('status_id','18');
        }
        if($request->has('pickupdate') && $request->pickupdate != null){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate)->where('status_id','18');
        }
        $CourierRequest = $CourierRequest->where('status_id','18')->orderBy('id','desc')->paginate(100);
        if (isset($request->pickupdate) || $request->courierid || $request->trackingid || $request->merchantname) {
            $render['pickupdate'] = $request->pickupdate;
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $render['merchantname'] = $request->merchantname;
            $CourierRequest = $CourierRequest->appends($render);
        }
        $data['courier_requests'] = $CourierRequest;

        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $data['courier_requests'];
        $data['statuses'] = Status::all();
        $data['delivered'] = "1";
        return view('courierRequests.index',$data);
    }

   

    public function completed(Request $request){
        $CourierRequest = new CourierRequest();

        if(($request->has('pickupdate') && $request->pickupdate != null) && ($request->has('merchantname') && $request->merchantname != null)){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate)->where('customer_id',$request->merchantname)->where('status_id','18')->where('cod_payment_status',"yes");
        } 
        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('id',$request->courierid)->where('status_id','18')->where('cod_payment_status',"yes");
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid)->where('status_id','18')->where('cod_payment_status',"yes");
        }
        if($request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('customer_id',$request->merchantname)->where('status_id','18')->where('cod_payment_status',"yes");
        }
        if($request->has('pickupdate') && $request->pickupdate != null){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate)->where('status_id','18')->where('cod_payment_status',"yes");
        }
        $CourierRequest = $CourierRequest->where('status_id','18')->where('cod_payment_status',"yes")->orderBy('id','desc')->paginate(100);
        if (isset($request->pickupdate) || $request->courierid || $request->trackingid || $request->merchantname) {
            $render['pickupdate'] = $request->pickupdate;
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $render['merchantname'] = $request->merchantname;
            $CourierRequest = $CourierRequest->appends($render);
        }
        $data['courier_requests'] = $CourierRequest;
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $data['courier_requests'];
        $data['complete'] = "1";
        $data['statuses'] = Status::all();
        return view('courierRequests.index',$data);
    }

    public function returned(Request $request){
        $CourierRequest = new CourierRequest();

        if(($request->has('pickupdate') && $request->pickupdate != null) && ($request->has('merchantname') && $request->merchantname != null)){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate)->where('customer_id',$request->merchantname)->where('status_id','20');
        } 
        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('id',$request->courierid)->where('status_id','20');
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid)->where('status_id','20');
        }
        if($request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('customer_id',$request->merchantname)->where('status_id','20');
        }
        if($request->has('pickupdate') && $request->pickupdate != null){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate)->where('status_id','20');
        }
        $CourierRequest = $CourierRequest->where('status_id','20')->orderBy('id','desc')->paginate(100);
        if (isset($request->pickupdate) || $request->courierid || $request->trackingid || $request->merchantname) {
            $render['pickupdate'] = $request->pickupdate;
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $render['merchantname'] = $request->merchantname;
            $CourierRequest = $CourierRequest->appends($render);
        }
        $data['courier_requests'] = $CourierRequest;

        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $data['courier_requests'];
        $data['returned'] = "1";
        $data['statuses'] = Status::all();
        return view('courierRequests.index',$data);
    }

    public function cancelled(Request $request){
        $CourierRequest = new CourierRequest();

        if(($request->has('pickupdate') && $request->pickupdate != null) && ($request->has('merchantname') && $request->merchantname != null)){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate)->where('customer_id',$request->merchantname)->where('status_id','12');
        } 
        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('id',$request->courierid)->where('status_id','12');
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid)->where('status_id','12');
        }
        if($request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('customer_id',$request->merchantname)->where('status_id','12');
        }
        if($request->has('pickupdate') && $request->pickupdate != null){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate)->where('status_id','12');
        }
        $CourierRequest = $CourierRequest->where('status_id','12')->orderBy('id','desc')->paginate(100);
        if (isset($request->pickupdate) || $request->courierid || $request->trackingid || $request->merchantname) {
            $render['pickupdate'] = $request->pickupdate;
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $render['merchantname'] = $request->merchantname;
            $CourierRequest = $CourierRequest->appends($render);
        }
        $data['courier_requests'] = $CourierRequest;

        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $data['courier_requests'];
        $data['cancelled'] = "1";
        $data['statuses'] = Status::all();
        return view('courierRequests.index',$data);
    }

    public function hold(Request $request){
        $CourierRequest = new CourierRequest();

        if(($request->has('pickupdate') && $request->pickupdate != null) && ($request->has('merchantname') && $request->merchantname != null)){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate)->where('customer_id',$request->merchantname)->where('status_id','17');
        } 
        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('id',$request->courierid)->where('status_id','17');
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid)->where('status_id','17');
        }
        if($request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('customer_id',$request->merchantname)->where('status_id','17');
        }
        if($request->has('pickupdate') && $request->pickupdate != null){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate)->where('status_id','17');
        }
        $CourierRequest = $CourierRequest->where('status_id','17')->orderBy('id','desc')->paginate(100);
        if (isset($request->pickupdate) || $request->courierid || $request->trackingid || $request->merchantname) {
            $render['pickupdate'] = $request->pickupdate;
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $render['merchantname'] = $request->merchantname;
            $CourierRequest = $CourierRequest->appends($render);
        }
        $data['courier_requests'] = $CourierRequest;

        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $data['courier_requests'];
        $data['hold'] = "1";
        $data['statuses'] = Status::all();
        return view('courierRequests.index',$data);
    }
    
    public function daily_accounts(){
        if (date('Y-m-d H:i:s') < date("Y-m-d 14:00:00")) {
            $to = date('Y-m-d 14:00:00');
            $from = date("Y-m-d 14:00:00", strtotime("-1 day"));
        } else {
            $from = date('Y-m-d 14:00:00');
            $to = date("Y-m-d 14:00:00", strtotime("+1 day"));
        }
        $data['courier_requests'] = CourierRequest::whereBetween('created_at',[$from, $to])->paginate(100);
        return view('courierRequests.daily_account',$data);
    }

    public function search(Request $request){
        $table = '<table width="100%" class="table table-striped table-bordered table-hover" id="example">
                    <thead>
                        <tr>
                            <th>Courier Id</th>
                            <th>Tracking Id</th>
                            <th>Merchant</th>
                            <th>COD Amount</th>
                            <th>Delivery Charge</th>
                            <th>Collectable</th>
                            <th>Mercahnt Payable</th>
                            <th>Deilvery Mode</th>
                            <th>Paid By</th>
                            <th>Pickup Hub</th>
                            <th>Delivery Hub</th>
                            <th>Status</th>
                            <th>Hub Payment</th>
                            <th>COD Payment</th>
                            <th>Pickup Time</th>
                            <th>Delivery Time</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>';

        if($request->ajax()){
            $start_date = $request->sdate.' '.$request->stime;
            $end_date = $request->edate.' '.$request->etime;
            $merchantname=$request->merchantname;
            $hubs=$request->hubs;

            //DB::raw('DATE(`created_at`)')
            if($request->type==4 && $merchantname && $start_date && $end_date && $hubs){
                if ($hubs == '3') {
                    $couriers = CourierRequest::where('customer_id',$merchantname)->where('courier_type_id',$hubs)->whereBetween('created_at',[$start_date,$end_date])->orderBy('id','desc')->get();
                }else{
                    $couriers = CourierRequest::where('customer_id',$merchantname)->where('courier_type_id','!=','3')->whereBetween('created_at',[$start_date,$end_date])->orderBy('id','desc')->get();
                }
                

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.(!empty($total_courier->delivery_mode->title)?$total_courier->delivery_mode->title:"Not Available").'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td>No data found1</td>
                                <td></td>
                                <td></td>
                                
                            </tr>
                    </tbody>';
                }
            } 
            elseif($request->type==1 && $merchantname && $start_date && $end_date){
                $couriers = CourierRequest::where('customer_id',$merchantname)->whereBetween('created_at',[$start_date,$end_date])->orderBy('id','desc')->get();

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.(!empty($total_courier->delivery_mode->title)?$total_courier->delivery_mode->title:"Not Available").'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td>No data found1</td>
                                <td></td>
                                <td></td>
                                
                            </tr>
                    </tbody>';
                }
            }
            elseif($request->type==5 && $hubs && $start_date && $end_date){
                $couriers = CourierRequest::where('customer_id',$merchantname)->whereBetween('created_at',[$start_date,$end_date])->orderBy('id','desc')->get();
                if ($hubs == '3') {
                    $couriers = CourierRequest::where('courier_type_id',$hubs)->whereBetween('created_at',[$start_date,$end_date])->orderBy('id','desc')->get();
                }else{
                    $couriers = CourierRequest::where('courier_type_id','!=','3')->whereBetween('created_at',[$start_date,$end_date])->orderBy('id','desc')->get();
                }
                

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.(!empty($total_courier->delivery_mode->title)?$total_courier->delivery_mode->title:"Not Available").'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td>No data found1</td>
                                <td></td>
                                <td></td>
                                
                            </tr>
                    </tbody>';
                }
            }
            elseif($request->type==2 && $start_date && $end_date){
                $couriers = CourierRequest::whereBetween('created_at',[$start_date,$end_date])->orderBy('id','desc')->get();
                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 
                   
                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.(!empty($total_courier->delivery_mode->title)?$total_courier->delivery_mode->title:"Not Available").'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                    }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td>No data found1</td>
                                <td></td>
                                <td></td>
                                
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==3 && $merchantname){
                $couriers = CourierRequest::where('customer_id',$merchantname)->orderBy('id','desc')->get();

                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                   
                    $table .= '<tr>
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.(!empty($total_courier->delivery_mode->title)?$total_courier->delivery_mode->title:"Not Available").'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                    }
               }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td>No data found1</td>
                                <td></td>
                                <td></td>
                                
                            </tr>
                    </tbody>';
               }
            }

            else{

            }
        }
        return json_encode($table);
    }
    
    function excel(){
        return Excel::download(new UsersExport, 'holisterbd.xlsx');
    }
    
    public function daily_delivery_accounts(){
        if (date('Y-m-d H:i:s') < date("Y-m-d 14:00:00")) {
            $to = date('Y-m-d 14:00:00');
            $from = date("Y-m-d 14:00:00", strtotime("-1 day"));
        } else {
            $from = date('Y-m-d 14:00:00');
            $to = date("Y-m-d 14:00:00", strtotime("+1 day"));
        }
        $data['courier_requests'] = CourierRequest::where('status_id','18')->whereBetween('delivery_date',[$from, $to])->paginate(100);
        return view('courierRequests.daily_delivery_account',$data);
    }

    public function deliverysearch(Request $request){
        $table = '<table width="100%" class="table table-striped table-bordered table-hover" id="example">
                    <thead>
                        <tr>
                            <th>Courier Id</th>
                            <th>Tracking Id</th>
                            <th>Merchant</th>
                            <th>Receiver Info</th>
                            <th>COD Amount</th>
                            <th>Delivery Charge</th>
                            <th>Collectable</th>
                            <th>Mercahnt Payable</th>
                            <th>Paid By</th>
                            <th>Pickup Hub</th>
                            <th>Delivery Hub</th>
                            <th>Status</th>
                            <th>Hub Payment</th>
                            <th>COD Payment</th>
                            <th>Preferred Method</th>
                            <th>Number</th>
                            <th>Pickup Time</th>
                            <th>Delivery Time</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>';

        if($request->ajax()){
            $start_date = $request->sdate.' '.$request->stime.':00';
            $end_date = $request->edate.' '.$request->etime.':00';
            $merchantname=$request->merchantname;
            $hubs=$request->hubs;

            //DB::raw('DATE(`created_at`)')
            if($request->type==4 && $merchantname && $start_date && $end_date && $hubs){
                
                if ($hubs == '3') {
                    $couriers = CourierRequest::where('customer_id',$merchantname)->where('status_id','18')->where('courier_type_id',$hubs)->whereBetween('delivery_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }else{
                    $couriers = CourierRequest::where('customer_id',$merchantname)->where('status_id','18')->where('courier_type_id','!=','3')->whereBetween('delivery_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }

                $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    if ($total_courier->cod_payment_status == "yes") {
                        $cod_payment_status = 'Yes';
                    }else{
                        $cod_payment_status = 'No';
                    }

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.
                                    (!empty($total_courier->receiver_name)?$total_courier->receiver_name:'Not Available').'<br>'.
                                    (!empty($total_courier->receiver_address)?$total_courier->receiver_address:'Not Available').'<br>'.
                                    (!empty($total_courier->receiver_phone)?$total_courier->receiver_phone:'Not Available')
                                .'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. $cod_payment_status .'</td>
                                <td>'.(!empty($courier_request->preferred_method) ? $courier_request->preferred_method : 'Not Available') .'</td>
                                <td>'.(!empty($courier_request->preferred_method_number) ? $courier_request->preferred_method_number : 'Not Available') .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==1 && $merchantname && $start_date && $end_date){
                $couriers = CourierRequest::where('customer_id',$merchantname)->where('status_id','18')->whereBetween('delivery_date',[$start_date,$end_date])->orderBy('id','desc')->get();

                $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    if ($total_courier->cod_payment_status == "yes") {
                        $cod_payment_status = 'Yes';
                    }else{
                        $cod_payment_status = 'No';
                    }


                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.
                                    (!empty($total_courier->receiver_name)?$total_courier->receiver_name:'Not Available').'<br>'.
                                    (!empty($total_courier->receiver_address)?$total_courier->receiver_address:'Not Available').'<br>'.
                                    (!empty($total_courier->receiver_phone)?$total_courier->receiver_phone:'Not Available')
                                .'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. $cod_payment_status.'</td>
                                <td>'.(!empty($courier_request->preferred_method) ? $courier_request->preferred_method : 'Not Available') .'</td>
                                <td>'.(!empty($courier_request->preferred_method_number) ? $courier_request->preferred_method_number : 'Not Available') .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==5 && $hubs && $start_date && $end_date){
                if ($hubs == '3') {
                    $couriers = CourierRequest::where('status_id','18')->where('courier_type_id',$hubs)->whereBetween('delivery_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }else{
                    $couriers = CourierRequest::where('status_id','18')->where('courier_type_id','!=','3')->whereBetween('delivery_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }

                $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    if ($total_courier->cod_payment_status == "yes") {
                        $cod_payment_status = 'Yes';
                    }else{
                        $cod_payment_status = 'No';
                    }


                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.
                                    (!empty($total_courier->receiver_name)?$total_courier->receiver_name:'Not Available').'<br>'.
                                    (!empty($total_courier->receiver_address)?$total_courier->receiver_address:'Not Available').'<br>'.
                                    (!empty($total_courier->receiver_phone)?$total_courier->receiver_phone:'Not Available')
                                .'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. $cod_payment_status.'</td>
                                <td>'.(!empty($courier_request->preferred_method) ? $courier_request->preferred_method : 'Not Available') .'</td>
                                <td>'.(!empty($courier_request->preferred_method_number) ? $courier_request->preferred_method_number : 'Not Available') .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==2 && $start_date && $end_date){
                $couriers = CourierRequest::where('status_id','18')->whereBetween('delivery_date',[$start_date, $end_date])->orderBy('id','desc')->get();
                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    if ($total_courier->cod_payment_status == "yes") {
                        $cod_payment_status = 'Yes';
                    }else{
                        $cod_payment_status = 'No';
                    }

                    
                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.
                                    (!empty($total_courier->receiver_name)?$total_courier->receiver_name:'Not Available').'<br>'.
                                    (!empty($total_courier->receiver_address)?$total_courier->receiver_address:'Not Available').'<br>'.
                                    (!empty($total_courier->receiver_phone)?$total_courier->receiver_phone:'Not Available')
                                .'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. $cod_payment_status.'</td>
                                <td>'.(!empty($courier_request->preferred_method) ? $courier_request->preferred_method : 'Not Available') .'</td>
                                <td>'.(!empty($courier_request->preferred_method_number) ? $courier_request->preferred_method_number : 'Not Available') .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                    }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==3 && $merchantname){
                $couriers = CourierRequest::where('customer_id',$merchantname)->where('status_id','18')->orderBy('id','desc')->get();

                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    if ($total_courier->cod_payment_status == "yes") {
                        $cod_payment_status = 'Yes';
                    }else{
                        $cod_payment_status = 'No';
                    }

            
                   
                    $table .= '<tr>
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.
                                    (!empty($total_courier->receiver_name)?$total_courier->receiver_name:'Not Available').'<br>'.
                                    (!empty($total_courier->receiver_address)?$total_courier->receiver_address:'Not Available').'<br>'.
                                    (!empty($total_courier->receiver_phone)?$total_courier->receiver_phone:'Not Available')
                                .'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. $cod_payment_status.'</td>
                                <td>'.(!empty($courier_request->preferred_method) ? $courier_request->preferred_method : 'Not Available') .'</td>
                                <td>'.(!empty($courier_request->preferred_method_number) ? $courier_request->preferred_method_number : 'Not Available') .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                    }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }

            else{

            }
        }
        return json_encode($table);
    }

    public function daily_transactions(){
        if (date('Y-m-d H:i:s') < date("Y-m-d 14:00:00")) {
            $to = date('Y-m-d 14:00:00');
            $from = date("Y-m-d 14:00:00", strtotime("-1 day"));
        } else {
            $from = date('Y-m-d 14:00:00');
            $to = date("Y-m-d 14:00:00", strtotime("+1 day"));
        }
        $data['courier_requests'] = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->whereBetween('cod_payment_status_date',[$from, $to])->paginate(100);
        return view('courierRequests.daily_transactions',$data);
    }

    public function transactionssearch(Request $request){
        $table = '<table width="100%" class="table table-striped table-bordered table-hover" id="example">
                    <thead>
                        <tr>
                            <th>Courier Id</th>
                            <th>Tracking Id</th>
                            <th>Merchant</th>
                            <th>COD Amount</th>
                            <th>Delivery Charge</th>
                            <th>Collectable</th>
                            <th>Mercahnt Payable</th>
                            <th>Paid By</th>
                            <th>Pickup Hub</th>
                            <th>Delivery Hub</th>
                            <th>Status</th>
                            <th>Hub Payment</th>
                            <th>COD Payment</th>
                            <th>Pickup Time</th>
                            <th>Delivery Time</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>';

        if($request->ajax()){
            $start_date = $request->sdate.' '.$request->stime;
            $end_date = $request->edate.' '.$request->etime;
            $merchantname=$request->merchantname;
            $hubs=$request->hubs;

            //DB::raw('DATE(`created_at`)')
            if($request->type==4 && $merchantname && $start_date && $end_date && $hubs){
                

                if ($hubs == '3') {
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->where('courier_type_id',$hubs)->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }else{
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->where('courier_type_id','!=','3')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==1 && $merchantname && $start_date && $end_date){
                $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==5 && $hubs && $start_date && $end_date){
                if ($hubs == '3') {
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('courier_type_id',$hubs)->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }else{
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('courier_type_id','!=','3')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==2 && $start_date && $end_date){
                $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                       /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 
                   
                    $table .= '<tr class="odd gradeX">
                                 <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>'; 
                    }
               }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==3 && $merchantname){
                $couriers = CourierRequest::where('customer_id',$merchantname)->where('status_id',"18")->where('cod_payment_status',"yes")->orderBy('id','desc')->get();

                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                   
                    $table .= '<tr>
                                 <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                    }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }

            else{

            }
        }
        return json_encode($table);
    }
    
    public function daily_hubpayment_accounts(){
        if (date('Y-m-d H:i:s') < date("Y-m-d 14:00:00")) {
            $to = date('Y-m-d 14:00:00');
            $from = date("Y-m-d 14:00:00", strtotime("-1 day"));
        } else {
            $from = date('Y-m-d 14:00:00');
            $to = date("Y-m-d 14:00:00", strtotime("+1 day"));
        }
        $data['courier_requests'] = CourierRequest::where('hub_payment','yes')->whereBetween('hub_payment_date',[$from, $to])->paginate(100);
        return view('courierRequests.daily_hubpayment',$data);
    }

    public function hubpaymentsearch(Request $request){
        $table = '<table width="100%" class="table table-striped table-bordered table-hover" id="example">
                    <thead>
                        <tr>
                            <th>Courier Id</th>
                            <th>Tracking Id</th>
                            <th>Merchant</th>
                            <th>COD Amount</th>
                            <th>Delivery Charge</th>
                            <th>Collectable</th>
                            <th>Mercahnt Payable</th>
                            <th>Paid By</th>
                            <th>Pickup Hub</th>
                            <th>Delivery Hub</th>
                            <th>Status</th>
                            <th>Hub Payment</th>
                            <th>COD Payment</th>
                            <th>Pickup Time</th> 
                            <th>Delivery Time</th> 
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>';

        if($request->ajax()){
            $start_date = $request->sdate.' '.$request->stime;
            $end_date = $request->edate.' '.$request->etime;
            $merchantname=$request->merchantname;

            //DB::raw('DATE(`created_at`)')
            if($request->type==1 && $merchantname && $start_date && $end_date){
                $couriers = CourierRequest::where('hub_payment','yes')->where('customer_id',$merchantname)->whereBetween('hub_payment_date',[$start_date,$end_date])->orderBy('id','desc')->get();

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==2 && $start_date && $end_date){
                $couriers = CourierRequest::where('hub_payment','yes')->whereBetween('hub_payment_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                       /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 
                   
                    $table .= '<tr class="odd gradeX">
                                 <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>'; 
                    }
               }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==3 && $merchantname){
                $couriers = CourierRequest::where('customer_id',$merchantname)->where('hub_payment','yes')->orderBy('id','desc')->get();

                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                   
                    $table .= '<tr>
                                 <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                    }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }

            else{

            }
        }
        return json_encode($table);
    }
    
    public function merchantduelist(Request $request, $id){
        $CourierRequest = new CourierRequest();

        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('id',$request->courierid)->where('status_id','18');
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid)->where('status_id','18');
        }
        
        $CourierRequest = $CourierRequest->where('customer_id',$id)->where('status_id','18')->orderBy('id','desc')->get();

        
        $data['courier_requests'] = $CourierRequest;
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $data['courier_requests'];
        $data['merchant_due'] = "1";
        $data['statuses'] = Status::all();
        return view('courierRequests.merchant_due',$data);
    }
    
    public function hubduelist(Request $request, $id){
        $CourierRequest = new CourierRequest();

        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('id',$request->courierid)->where('status_id','18');
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid)->where('status_id','18');
        }
        /*$CourierRequest = $CourierRequest->where('delivery_hub',$id)->where('status_id','18')->where('hub_payment','no')->orderBy('id','desc')->paginate(100);*/
        $CourierRequest = $CourierRequest->where('status_id','18')->where('hub_payment','no')
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
        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $data['courier_requests'];
        $data['hub_due'] = "1";
        $data['statuses'] = Status::all();
        return view('courierRequests.hub_due',$data);
    }
    
    public function daily_bkash_transactions(){
        if (date('Y-m-d H:i:s') < date("Y-m-d 14:00:00")) {
            $to = date('Y-m-d 14:00:00');
            $from = date("Y-m-d 14:00:00", strtotime("-1 day"));
        } else {
            $from = date('Y-m-d 14:00:00');
            $to = date("Y-m-d 14:00:00", strtotime("+1 day"));
        }
        $data['courier_requests'] = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','bkash')->whereBetween('cod_payment_status_date',[$from, $to])->paginate(100);
        return view('courierRequests.daily_bkash_transactions',$data);
    }

    public function search_bkash_transactions(Request $request){
        $table = '<table width="100%" class="table table-striped table-bordered table-hover" id="example">
                    <thead>
                        <tr>
                            <th>Courier Id</th>
                            <th>Tracking Id</th>
                            <th>Merchant</th>
                            <th>COD Amount</th>
                            <th>Delivery Charge</th>
                            <th>Collectable</th>
                            <th>Mercahnt Payable</th>
                            <th>Paid By</th>
                            <th>Pickup Hub</th>
                            <th>Delivery Hub</th>
                            <th>Status</th>
                            <th>Hub Payment</th>
                            <th>COD Payment</th>
                            <th>Pickup Time</th>
                            <th>Delivery Time</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>';

        if($request->ajax()){
            $start_date = $request->sdate.' '.$request->stime;
            $end_date = $request->edate.' '.$request->etime;
            $merchantname=$request->merchantname;
            $hubs=$request->hubs;

            //DB::raw('DATE(`created_at`)')
             if($request->type==4 && $merchantname && $start_date && $end_date && $hubs){
                if ($hubs == '3') {
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->where('preferred_method','bkash')->where('courier_type_id',$hubs)->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }else{
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->where('preferred_method','bkash')->where('courier_type_id','!=','3')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==1 && $merchantname && $start_date && $end_date){
                $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->where('preferred_method','bkash')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==5 && $hubs && $start_date && $end_date){
                if ($hubs == '3') {
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','bkash')->where('courier_type_id',$hubs)->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }else{
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','bkash')->where('courier_type_id','!=','3')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==2 && $start_date && $end_date){
                $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','bkash')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                       /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 
                   
                    $table .= '<tr class="odd gradeX">
                                 <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>'; 
                    }
               }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==3 && $merchantname){
                $couriers = CourierRequest::where('customer_id',$merchantname)->where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','bkash')->orderBy('id','desc')->get();

                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                   
                    $table .= '<tr>
                                 <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                    }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }

            else{

            }
        }
        return json_encode($table);
    }

    public function daily_nagad_transactions(){
        if (date('Y-m-d H:i:s') < date("Y-m-d 14:00:00")) {
            $to = date('Y-m-d 14:00:00');
            $from = date("Y-m-d 14:00:00", strtotime("-1 day"));
        } else {
            $from = date('Y-m-d 14:00:00');
            $to = date("Y-m-d 14:00:00", strtotime("+1 day"));
        }
        $data['courier_requests'] = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','nagad')->whereBetween('cod_payment_status_date',[$from, $to])->paginate(100);
        return view('courierRequests.daily_nagad_transactions',$data);
    }

    public function search_nagad_transactions(Request $request){
        $table = '<table width="100%" class="table table-striped table-bordered table-hover" id="example">
                    <thead>
                        <tr>
                            <th>Courier Id</th>
                            <th>Tracking Id</th>
                            <th>Merchant</th>
                            <th>COD Amount</th>
                            <th>Delivery Charge</th>
                            <th>Collectable</th>
                            <th>Mercahnt Payable</th>
                            <th>Paid By</th>
                            <th>Pickup Hub</th>
                            <th>Delivery Hub</th>
                            <th>Status</th>
                            <th>Hub Payment</th>
                            <th>COD Payment</th>
                            <th>Pickup Time</th>
                            <th>Delivery Time</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>';

        if($request->ajax()){
            $start_date = $request->sdate.' '.$request->stime;
            $end_date = $request->edate.' '.$request->etime;
            $merchantname=$request->merchantname;
            $hubs=$request->hubs;

            //DB::raw('DATE(`created_at`)')
            if($request->type==4 && $merchantname && $start_date && $end_date && $hubs){
                
                if ($hubs == '3') {

                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->where('preferred_method','nagad')->where('courier_type_id',$hubs)->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();

                }else{
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->where('preferred_method','nagad')->where('courier_type_id','!=','3')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            else if($request->type==1 && $merchantname && $start_date && $end_date){
                $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->where('preferred_method','nagad')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            else if($request->type==5 && $hubs && $start_date && $end_date){
                if ($hubs == '3') {

                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','nagad')->where('courier_type_id',$hubs)->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();

                }else{
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','nagad')->where('courier_type_id','!=','3')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==2 && $start_date && $end_date){
                $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','nagad')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                       /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 
                   
                    $table .= '<tr class="odd gradeX">
                                 <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>'; 
                    }
               }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==3 && $merchantname){
                $couriers = CourierRequest::where('customer_id',$merchantname)->where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','nagad')->orderBy('id','desc')->get();

                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                   
                    $table .= '<tr>
                                 <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                    }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }

            else{

            }
        }
        return json_encode($table);
    }

    public function daily_rocket_transactions(){
        if (date('Y-m-d H:i:s') < date("Y-m-d 14:00:00")) {
            $to = date('Y-m-d 14:00:00');
            $from = date("Y-m-d 14:00:00", strtotime("-1 day"));
        } else {
            $from = date('Y-m-d 14:00:00');
            $to = date("Y-m-d 14:00:00", strtotime("+1 day"));
        }
        $data['courier_requests'] = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','rocket')->whereBetween('cod_payment_status_date',[$from, $to])->paginate(100);
        return view('courierRequests.daily_rocket_transactions',$data);
    }

    public function search_rocket_transactions(Request $request){
        $table = '<table width="100%" class="table table-striped table-bordered table-hover" id="example">
                    <thead>
                        <tr>
                            <th>Courier Id</th>
                            <th>Tracking Id</th>
                            <th>Merchant</th>
                            <th>COD Amount</th>
                            <th>Delivery Charge</th>
                            <th>Collectable</th>
                            <th>Mercahnt Payable</th>
                            <th>Paid By</th>
                            <th>Pickup Hub</th>
                            <th>Delivery Hub</th>
                            <th>Status</th>
                            <th>Hub Payment</th>
                            <th>COD Payment</th>
                            <th>Pickup Time</th>
                            <th>Delivery Time</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>';

        if($request->ajax()){
            $start_date = $request->sdate.' '.$request->stime;
            $end_date = $request->edate.' '.$request->etime;
            $merchantname=$request->merchantname;
            $hubs=$request->hubs;

            //DB::raw('DATE(`created_at`)')
            if($request->type==4 && $merchantname && $start_date && $end_date && $hubs){
                
                if ($hubs == '3') {
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->where('preferred_method','rocket')->where('courier_type_id',$hubs)->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }else{
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->where('preferred_method','rocket')->where('courier_type_id','!=','3')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            else if($request->type==1 && $merchantname && $start_date && $end_date){
                $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->where('preferred_method','rocket')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            else if($request->type==5 && $hubs && $start_date && $end_date){
                if ($hubs == '3') {
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','rocket')->where('courier_type_id',$hubs)->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }else{
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','rocket')->where('courier_type_id','!=','3')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==2 && $start_date && $end_date){
                $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','rocket')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                       /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 
                   
                    $table .= '<tr class="odd gradeX">
                                 <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>'; 
                    }
               }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==3 && $merchantname){
                $couriers = CourierRequest::where('customer_id',$merchantname)->where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','rocket')->orderBy('id','desc')->get();

                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                   
                    $table .= '<tr>
                                 <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                    }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }

            else{

            }
        }
        return json_encode($table);
    }

    public function daily_bank_transactions(){
        if (date('Y-m-d H:i:s') < date("Y-m-d 14:00:00")) {
            $to = date('Y-m-d 14:00:00');
            $from = date("Y-m-d 14:00:00", strtotime("-1 day"));
        } else {
            $from = date('Y-m-d 14:00:00');
            $to = date("Y-m-d 14:00:00", strtotime("+1 day"));
        }
        $data['courier_requests'] = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','bank')->whereBetween('cod_payment_status_date',[$from, $to])->paginate(100);
        return view('courierRequests.daily_bank_transactions',$data);
    }

    public function search_bank_transactions(Request $request){
        $table = '<table width="100%" class="table table-striped table-bordered table-hover" id="example">
                    <thead>
                        <tr>
                            <th>Courier Id</th>
                            <th>Tracking Id</th>
                            <th>Merchant</th>
                            <th>COD Amount</th>
                            <th>Delivery Charge</th>
                            <th>Collectable</th>
                            <th>Mercahnt Payable</th>
                            <th>Paid By</th>
                            <th>Pickup Hub</th>
                            <th>Delivery Hub</th>
                            <th>Status</th>
                            <th>Hub Payment</th>
                            <th>COD Payment</th>
                            <th>Pickup Time</th>
                            <th>Delivery Time</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>';

        if($request->ajax()){
            $start_date = $request->sdate.' '.$request->stime;
            $end_date = $request->edate.' '.$request->etime;
            $merchantname=$request->merchantname;
            $hubs=$request->hubs;

            //DB::raw('DATE(`created_at`)')
            if($request->type==4 && $merchantname && $start_date && $end_date && $hubs){
                
                if ($hubs == '3') {
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->where('preferred_method','bank')->where('courier_type_id',$hubs)->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }else{
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->where('preferred_method','bank')->where('courier_type_id','!=','3')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==1 && $merchantname && $start_date && $end_date){
                $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('customer_id',$merchantname)->where('preferred_method','bank')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==5 && $hubs && $start_date && $end_date){
                if ($hubs == '3') {
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','bank')->where('courier_type_id',$hubs)->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }else{
                    $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','bank')->where('courier_type_id','!=','3')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                }

            $total_couriers=$couriers->count();
               if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                    $table .= '<tr class="odd gradeX">
                                <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                   }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==2 && $start_date && $end_date){
                $couriers = CourierRequest::where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','bank')->whereBetween('cod_payment_status_date',[$start_date,$end_date])->orderBy('id','desc')->get();
                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                       /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 
                   
                    $table .= '<tr class="odd gradeX">
                                 <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>'; 
                    }
               }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }
            elseif($request->type==3 && $merchantname){
                $couriers = CourierRequest::where('customer_id',$merchantname)->where('status_id',"18")->where('cod_payment_status',"yes")->where('preferred_method','bank')->orderBy('id','desc')->get();

                $total_couriers=$couriers->count();
                if($total_couriers>0){
                    foreach($couriers as $total_courier){
                        /*collectable*/
                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $collectable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $collectable = $total_courier->cash_on_delivery_amount +  $total_courier->pricing->price;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $collectable = $total_courier->cash_on_delivery_amount; 
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod" || $total_courier->paid_by == "sender"){
                            $collectable = 0;
                        }
                        else{
                            $collectable = $total_courier->pricing->price;
                        }
                    }

                    /*payable*/

                    if(!empty($total_courier->cash_on_delivery_amount)){
                        if($total_courier->paid_by == "sender"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "receiver"){
                            $payable = $total_courier->cash_on_delivery_amount;
                        }elseif($total_courier->paid_by == "merged_with_cod"){
                            $payable = $total_courier->cash_on_delivery_amount -  $total_courier->pricing->price;
                        }
                    }else{
                        if($total_courier->paid_by == "merged_with_cod"){
                            $payable = 0 -  $total_courier->pricing->price;
                        }else{
                            $payable = 0;
                        }
                    }

                    /*Delivery Hub*/
                    if($total_courier->delivery_hub == '0' || empty($total_courier->delivery_hub)){
                        $delivery_hub = (!empty($total_courier->branch->name)?$total_courier->branch->name:'Not Available');
                    }else{
                        $delivery_hub=(!empty(\App\Models\Branch::find($total_courier->delivery_hub)->name)?\App\Models\Branch::find($total_courier->delivery_hub)->name:'Not Available');
                    } 

                   
                    $table .= '<tr>
                                 <td>'.$total_courier->id.'</td>
                                <td>'.$total_courier->tracking_id.'</td>
                                <td>'.(!empty($total_courier->customer->name) ? $total_courier->customer->name : 'Not Available').'</td>
                                <td>'.(!empty($total_courier->cash_on_delivery_amount)?$total_courier->cash_on_delivery_amount:0).'</td>
                                <td>'.(!empty($total_courier->pricing->price)?$total_courier->pricing->price:0).'</td>
                                <td>'.$collectable.'</td>
                                <td>'.$payable.'</td>
                                <td>'.$total_courier->paid_by.'</td>
                                <td>'.(!empty($total_courier->branch->name)?$total_courier->branch->name:"Not Available").'</td>
                                <td>'.(!empty($delivery_hub)?$delivery_hub:"Not Available").'</td>
                                <td>'.(!empty($total_courier->status->name)?$total_courier->status->name:"Not Available").'</td>
                                <td>'.(!empty($total_courier->hub_payment)?$total_courier->hub_payment:"Not Available").'</td>
                                <td>'. (!empty($total_courier->cash_on_delivery_amount)?($total_courier->cod_payment_status == "yes"?"Yes":"No"):"Not Available") .'</td>
                                <td>'.
                                    date('d-m-Y h:i A',strtotime($total_courier->created_at))
                                .'</td>
                                <td>'.
                                    (!empty($total_courier->delivery_date)?date('d-m-Y h:i A',strtotime($total_courier->delivery_date)):"Not Available")
                                .'</td>
                                <td>
                                    <a href="'.route('dashboard.courier.request.info',$total_courier->id).'" class="btn btn-sm btn-outline btn-default">Details</a>
                                    <a href="'.route('courier-request-printer',$total_courier->id).'" class="btn btn-sm btn-primary btn-default">Print</a>
                                </td>
                            </tr>';
                        
                    }
                }else{
                $table.='<tbody>
                            <tr class="odd gradeX">
                                <td colspan="13">No data found</td>
                            </tr>
                    </tbody>';
               }
            }

            else{

            }
        }
        return json_encode($table);
    }
    
    public function bkash_merchant_payable(Request $request){
        $CourierRequest = new CourierRequest();

        if($request->has('hubs') && $request->hubs != null && $request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('courier_type_id',$request->hubs)->where('customer_id',$request->merchantname)->where('status_id','18');
        }
        
        if($request->has('hubs') && $request->hubs != null){
            $CourierRequest = $CourierRequest->where('courier_type_id',$request->hubs)->where('status_id','18');
        }

        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('courier_requests.id',$request->courierid)->where('status_id','18');
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid)->where('status_id','18');
        }
        if($request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('customer_id',$request->merchantname)->where('status_id','18');
        }
        
        $CourierRequest = $CourierRequest->join('users','users.id','courier_requests.customer_id')->where('status_id','18')->where('cash_on_delivery_amount','>','0')->where('hub_payment','yes')
            ->where(function($query){
                    return $query
                    ->whereNull('courier_requests.cod_payment_status')
                    ->orWhere('courier_requests.cod_payment_status', 'no');
                })
        ->where('users.preferred_method','bkash')->orderBy('courier_requests.id','desc')->select('courier_requests.*','users.preferred_method','users.bkash_no','users.nagad_no','users.rocket_no','users.bank_ac_no','users.bank_name')->paginate(100);

        if (isset($request->courierid) || $request->trackingid || $request->merchantname || $request->hubs) {
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $render['merchantname'] = $request->merchantname;
            $CourierRequest = $CourierRequest->appends($render);
        }

        $data['courier_requests'] = $CourierRequest;

        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $data['courier_requests'];
        $data['statuses'] = Status::all();
        $data['remaining'] = "1";
        return view('courierRequests.merchant_payable',$data);
    }

    public function nagad_merchant_payable(Request $request){
        $CourierRequest = new CourierRequest();
        if($request->has('hubs') && $request->hubs != null && $request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('courier_type_id',$request->hubs)->where('customer_id',$request->merchantname)->where('status_id','18');
        }
        if($request->has('hubs') && $request->hubs != null){
            $CourierRequest = $CourierRequest->where('courier_type_id',$request->hubs)->where('status_id','18');
        }
        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('courier_requests.id',$request->courierid)->where('status_id','18');
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid)->where('status_id','18');
        }
        if($request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('customer_id',$request->merchantname)->where('status_id','18');
        }
        
        $CourierRequest = $CourierRequest->join('users','users.id','courier_requests.customer_id')->where('status_id','18')->where('cash_on_delivery_amount','>','0')->where('hub_payment','yes')
            ->where(function($query){
                    return $query
                    ->whereNull('courier_requests.cod_payment_status')
                    ->orWhere('courier_requests.cod_payment_status', 'no');
                })
        ->where('users.preferred_method','nagad')->orderBy('courier_requests.id','desc')->select('courier_requests.*','users.preferred_method','users.bkash_no','users.nagad_no','users.rocket_no','users.bank_ac_no','users.bank_name')->paginate(100);

        if (isset($request->courierid) || $request->trackingid || $request->merchantname || $request->hubs) {
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $render['merchantname'] = $request->merchantname;
            $CourierRequest = $CourierRequest->appends($render);
        }

        $data['courier_requests'] = $CourierRequest;

        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $data['courier_requests'];
        $data['statuses'] = Status::all();
        $data['remaining'] = "1";
        return view('courierRequests.merchant_payable',$data);
    }

    public function rocket_merchant_payable(Request $request){
        $CourierRequest = new CourierRequest();

        if($request->has('hubs') && $request->hubs != null && $request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('courier_type_id',$request->hubs)->where('customer_id',$request->merchantname)->where('status_id','18');
        }
        if($request->has('hubs') && $request->hubs != null){
            $CourierRequest = $CourierRequest->where('courier_type_id',$request->hubs)->where('status_id','18');
        }

        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('courier_requests.id',$request->courierid)->where('status_id','18');
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid)->where('status_id','18');
        }
        if($request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('customer_id',$request->merchantname)->where('status_id','18');
        }
        
        $CourierRequest = $CourierRequest->join('users','users.id','courier_requests.customer_id')->where('status_id','18')->where('cash_on_delivery_amount','>','0')->where('hub_payment','yes')
            ->where(function($query){
                    return $query
                    ->whereNull('courier_requests.cod_payment_status')
                    ->orWhere('courier_requests.cod_payment_status', 'no');
                })
        ->where('users.preferred_method','rocket')->orderBy('courier_requests.id','desc')->select('courier_requests.*','users.preferred_method','users.bkash_no','users.nagad_no','users.rocket_no','users.bank_ac_no','users.bank_name')->paginate(100);

        if (isset($request->courierid) || $request->trackingid || $request->merchantname || $request->hubs) {
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $render['merchantname'] = $request->merchantname;
            $CourierRequest = $CourierRequest->appends($render);
        }

        $data['courier_requests'] = $CourierRequest;

        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $data['courier_requests'];
        $data['statuses'] = Status::all();
        $data['remaining'] = "1";
        return view('courierRequests.merchant_payable',$data);
    }

    public function bank_merchant_payable(Request $request){
        $CourierRequest = new CourierRequest();
        if($request->has('hubs') && $request->hubs != null && $request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('courier_type_id',$request->hubs)->where('customer_id',$request->merchantname)->where('status_id','18');
        }
        if($request->has('hubs') && $request->hubs != null){
            $CourierRequest = $CourierRequest->where('courier_type_id',$request->hubs)->where('status_id','18');
        }
        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('courier_requests.id',$request->courierid)->where('status_id','18');
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid)->where('status_id','18');
        }
        if($request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('customer_id',$request->merchantname)->where('status_id','18');
        }
        
        $CourierRequest = $CourierRequest->join('users','users.id','courier_requests.customer_id')->where('status_id','18')->where('cash_on_delivery_amount','>','0')->where('hub_payment','yes')
            ->where(function($query){
                    return $query
                    ->whereNull('courier_requests.cod_payment_status')
                    ->orWhere('courier_requests.cod_payment_status', 'no');
                })
        ->where('users.preferred_method','bank')->orderBy('courier_requests.id','desc')->select('courier_requests.*','users.preferred_method','users.bkash_no','users.nagad_no','users.rocket_no','users.bank_ac_no','users.bank_name')->paginate(100);

        if (isset($request->courierid) || $request->trackingid || $request->merchantname || $request->hubs) {
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $render['merchantname'] = $request->merchantname;
            $CourierRequest = $CourierRequest->appends($render);
        }

        $data['courier_requests'] = $CourierRequest;

        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $data['courier_requests'];
        $data['statuses'] = Status::all();
        $data['remaining'] = "1";
        return view('courierRequests.merchant_payable',$data);
    }

    public function all_merchant_payable(Request $request){
        $CourierRequest = new CourierRequest();
        if($request->has('hubs') && $request->hubs != null && $request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('courier_type_id',$request->hubs)->where('customer_id',$request->merchantname)->where('status_id','18');
        }
        if($request->has('hubs') && $request->hubs != null){
            $CourierRequest = $CourierRequest->where('courier_type_id',$request->hubs)->where('status_id','18');
        }
        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('courier_requests.id',$request->courierid)->where('status_id','18');
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid)->where('status_id','18');
        }
        if($request->has('merchantname') && $request->merchantname != null){
            $CourierRequest = $CourierRequest->where('customer_id',$request->merchantname)->where('status_id','18');
        }
        
        $CourierRequest = $CourierRequest->join('users','users.id','courier_requests.customer_id')->where('status_id','18')->where('cash_on_delivery_amount','>','0')->where('hub_payment','yes')
            ->where(function($query){
                    return $query
                    ->whereNull('courier_requests.cod_payment_status')
                    ->orWhere('courier_requests.cod_payment_status', 'no');
                })
        ->orderBy('courier_requests.id','desc')->select('courier_requests.*','users.preferred_method','users.bkash_no','users.nagad_no','users.rocket_no','users.bank_ac_no','users.bank_name')->paginate(100);

        if (isset($request->courierid) || $request->trackingid || $request->merchantname || $request->hubs) {
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $render['merchantname'] = $request->merchantname;
            $CourierRequest = $CourierRequest->appends($render);
        }

        $data['courier_requests'] = $CourierRequest;

        $res = new \stdClass();
        $res->error = false;
        $res->message = "Courier requests loaded!";
        $res->data = $data['courier_requests'];
        $data['statuses'] = Status::all();
        $data['remaining'] = "1";
        return view('courierRequests.merchant_payable',$data);
    }
    
    
    public function pickup_request(){
        $pickup = CourierRequest::where('branch_id',Auth::user()->hub_id)->with('receiver_area','sender_area','branch',
                'receiver_city','sender_city','status','pricing','customer','rider','delivery')->get();
        $response = [
            'msg' => 'Pickup',
            'pickup' => $pickup
        ];
        return response()->json($response, 200);
    }

    public function delivery_request(){
        $delivery = CourierRequest::where('delivery_hub',Auth::user()->hub_id)->with('receiver_area','sender_area','branch',
                'receiver_city','sender_city','status','pricing','customer','rider')->get();
        $response = [
            'msg' => 'Delivery',
            'Delivery' => $delivery
        ];
        return response()->json($response, 200);
    }

    public function hub_transfers_id($id){
        $data['courier'] = CourierRequest::find($id);
        $data['hub_transfers'] = HubTransfer::where('courier_id',$id)->get();
        $data['hubs'] = Branch::get();
        $response = [
            'msg' => 'Hub Transfer Details',
            'data' => $data
        ];
        return response()->json($response, 200);
    }

    public function hub_transfers_id_store(Request $request, $id){
        $hubs['hub_status'] = $request->hub_status;
        if ($request->hub_status == "1") {
            $hubs['delivery_hub'] = $request->hub_id;
        }
        if ($request->hub_status == "2") {
            $hubs['transit_hub'] = $request->hub_id;
        }
        CourierRequest::where('id',$id)->update($hubs);

        $transfer['courier_id'] = $id;
        $transfer['hub_id'] = $request->hub_id;
        $transfer['hub_status'] = $request->hub_status;
        HubTransfer::create($transfer);

        $response = [
            'msg' => 'Successfully Updated'
        ];
        return response()->json($response, 200);
    }

    public function hub_rider(){
        $rider = User::where('rider_hub',Auth::user()->hub_id)->select('id','name','rider_hub','type')->get();
        $response = [
            'msg' => 'Rider Info',
            'rider' => $rider
        ];
        return response()->json($response, 200);
    }
    
    public function states(Request $request){
        $today_date = Carbon::today();
        if (date('Y-m-d H:i:s') < date("Y-m-d 14:00:00")) {
            $to = date('Y-m-d 14:00:00');
            $from = date("Y-m-d 14:00:00", strtotime("-1 day"));
        } else {
            $from = date('Y-m-d 14:00:00');
            $to = date("Y-m-d 14:00:00", strtotime("+1 day"));
        }

        /*Parcel Entry*/
        $daily_entry_count = count(CourierRequest::where('branch_id',Auth::user()->hub_id)->where('status_id','!=','12')->whereBetween('created_at',[$from, $to])->get());
        $daily_cancel_entry_count = count(CourierRequest::where('branch_id',Auth::user()->hub_id)->where('status_id','12')->whereBetween('created_at',[$from, $to])->get());

        $total_entry_count = count(CourierRequest::where('branch_id',Auth::user()->hub_id)->where('status_id','!=','12')->get());
        $total_cancel_entry_count = count(CourierRequest::where('branch_id',Auth::user()->hub_id)->where('status_id','12')->get());

        $today_pickupdelivery_parcel = count(CourierRequest::where('status_id','18')->where('delivery_hub','0')->where('branch_id',Auth::user()->hub_id)->whereBetween('delivery_date',[$from, $to])->get());
        $today_deliveredparcel = count(CourierRequest::where('status_id','18')->where('delivery_hub',Auth::user()->hub_id)->whereBetween('delivery_date',[$from, $to])->get());
        $total_delivered_parcel = $today_pickupdelivery_parcel + $today_deliveredparcel;

        $total_pickupdelivery_parcel = count(CourierRequest::where('status_id','18')->where('delivery_hub','0')->where('branch_id',Auth::user()->hub_id)->get());
        $total_deliveredparcel = count(CourierRequest::where('status_id','18')->where('delivery_hub',Auth::user()->hub_id)->get());
        $total_delivered_till_now_parcel = $total_pickupdelivery_parcel + $total_deliveredparcel;
        /*End of Parcel Entry*/

        /*Delivery Charge*/

        $today_reciever1 = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.paid_by','!=','sender')->where('courier_requests.delivery_hub',Auth::user()->hub_id)->whereBetween('courier_requests.delivery_date',[$from, $to])->sum('pricings.price');
         $today_reciever2 = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.paid_by','!=','sender')->where('courier_requests.delivery_hub','0')->where('courier_requests.branch_id',Auth::user()->hub_id)->whereBetween('courier_requests.delivery_date',[$from, $to])->sum('pricings.price');
         $today_reciever3 = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.paid_by','sender')->where('courier_requests.branch_id',Auth::user()->hub_id)->whereBetween('courier_requests.delivery_date',[$from, $to])->sum('pricings.price');
        $today_cod_collect = $today_reciever1 + $today_reciever2 + $today_reciever3;

        $total_reciever1 = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.paid_by','!=','sender')->where('courier_requests.delivery_hub',Auth::user()->hub_id)->sum('pricings.price');
         $total_reciever2 = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.paid_by','!=','sender')->where('courier_requests.delivery_hub','0')->where('courier_requests.branch_id',Auth::user()->hub_id)->sum('pricings.price');
         $total_reciever3 = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.paid_by','sender')->where('courier_requests.branch_id',Auth::user()->hub_id)->sum('pricings.price');
        $total_receivable_amount = $total_reciever1 + $total_reciever2 + $total_reciever3;

        $total_recieverhub1 = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.paid_by','!=','sender')->where('courier_requests.delivery_hub',Auth::user()->hub_id)->where('courier_requests.hub_payment','yes')->sum('pricings.price');
         $total_recieverhub2 = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.paid_by','!=','sender')->where('courier_requests.delivery_hub','0')->where('courier_requests.branch_id',Auth::user()->hub_id)->where('courier_requests.hub_payment','yes')->sum('pricings.price');
         $total_recieverhub3 = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.paid_by','sender')->where('courier_requests.branch_id',Auth::user()->hub_id)->where('courier_requests.hub_payment','yes')->sum('pricings.price');
        $total_receivable_amount_hub = $total_recieverhub1 + $total_recieverhub2 + $total_recieverhub3;

        /*End of Delivery Charge*/

        /*COD*/

        /*COD Collected Today*/
         $deliveryhub_cod = CourierRequest::where('status_id','18')->where('delivery_hub',Auth::user()->hub_id)->whereBetween('delivery_date',[$from, $to])->sum('cash_on_delivery_amount');
         $deliveryhub_mergewithcod = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.paid_by','merged_with_cod')->where('courier_requests.delivery_hub',Auth::user()->hub_id)->whereBetween('courier_requests.delivery_date',[$from, $to])->sum('pricings.price');
         $delivery_hub_cod = $deliveryhub_cod - $deliveryhub_mergewithcod;

        $pickuphub_cod = CourierRequest::where('status_id','18')->where('delivery_hub','0')->where('branch_id',Auth::user()->hub_id)->whereBetween('delivery_date',[$from, $to])->sum('cash_on_delivery_amount');
         $pickuphub_mergewithcod = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.delivery_hub','0')->where('courier_requests.branch_id',Auth::user()->hub_id)->where('courier_requests.paid_by','merged_with_cod')->whereBetween('courier_requests.delivery_date',[$from, $to])->sum('pricings.price');
         $pickup_hub_cod = $pickuphub_cod - $pickuphub_mergewithcod;
        $today_cod_amount = $delivery_hub_cod + $pickup_hub_cod;
        /*COD Collected Today*/

        /*COD Collected Till Now*/
        $deliveryhub_cod_total = CourierRequest::where('status_id','18')->where('delivery_hub',Auth::user()->hub_id)->sum('cash_on_delivery_amount');
         $deliveryhub_mergewithcod_total = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.delivery_hub',Auth::user()->hub_id)->where('courier_requests.paid_by','merged_with_cod')->sum('pricings.price');
         $delivery_hub_cod_total = $deliveryhub_cod_total - $deliveryhub_mergewithcod_total;

        $pickuphub_cod_total = CourierRequest::where('status_id','18')->where('delivery_hub','0')->where('branch_id',Auth::user()->hub_id)->sum('cash_on_delivery_amount');
         $pickuphub_mergewithcod_total = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.delivery_hub','0')->where('courier_requests.paid_by','merged_with_cod')->where('courier_requests.branch_id',Auth::user()->hub_id)->sum('pricings.price');
         $pickup_hub_cod_total = $pickuphub_cod_total - $pickuphub_mergewithcod_total;
        $total_cod_all_amount = $delivery_hub_cod_total + $pickup_hub_cod_total;
        /*COD Collected Till Now*/

        /*COD Collected By Admin Till Now*/

        $deliveryhub_cod_total_hub = CourierRequest::where('status_id','18')->where('delivery_hub',Auth::user()->hub_id)->where('hub_payment','yes')->sum('cash_on_delivery_amount');
         $deliveryhub_mergewithcod_total_hub = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.delivery_hub',Auth::user()->hub_id)->where('courier_requests.paid_by','merged_with_cod')->where('courier_requests.hub_payment','yes')->sum('pricings.price');
         $delivery_hub_cod_total_hub = $deliveryhub_cod_total_hub - $deliveryhub_mergewithcod_total_hub;

        $pickuphub_cod_total_hub = CourierRequest::where('status_id','18')->where('delivery_hub','0')->where('branch_id',Auth::user()->hub_id)->where('hub_payment','yes')->sum('cash_on_delivery_amount');
         $pickuphub_mergewithcod_total_hub = CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('courier_requests.delivery_hub','0')->where('courier_requests.branch_id',Auth::user()->hub_id)->where('courier_requests.paid_by','merged_with_cod')->where('courier_requests.hub_payment','yes')->sum('pricings.price');
         $pickup_hub_cod_total_hub = $pickuphub_cod_total_hub - $pickuphub_mergewithcod_total_hub;
        $total_cod_all_amount_hub = $delivery_hub_cod_total_hub + $pickup_hub_cod_total_hub;
        
        /*COD Collected By Admin Till Now*/
        /*End ofCOD*/
        $response = [
            'msg' => 'Info',
            'Total Pickup Parcel Entry Till Now' => $total_entry_count,
            'Todays Pickup Parcel Entry' => $daily_entry_count,
            'Todays Cancel Parcel' => $daily_cancel_entry_count,
            'Total Cancel Parcel Till Now' => $total_cancel_entry_count,
            'Total Delivered Today' => $total_delivered_parcel,
            'Total Delivered Till Now' => $total_delivered_till_now_parcel,
            'Delivery Charge Collected Till Now' => $total_receivable_amount,
            'Delivery Charge Collected Today' => $today_cod_collect,
            'COD Collected Today' => $today_cod_amount,
            'Delivery Charge Collected By Admin Till Now' => $total_receivable_amount_hub,
            'COD Collected Till Now' => $total_cod_all_amount,
            'COD Collected By Admin Till Now' => $total_cod_all_amount_hub,

        ];
        return response()->json($response, 200);
    }
    
    public function bulk_hub_transfers_id_store(Request $request){

        foreach ($request->courier_id as $id) {
            $hubs['hub_status'] = $request->hub_status;
            if ($request->hub_status == "1") {
                $hubs['delivery_hub'] = $request->hub_id;
            }
            if ($request->hub_status == "2") {
                $hubs['transit_hub'] = $request->hub_id;
            }
            CourierRequest::where('id',$id)->update($hubs);

            $transfer['courier_id'] = $id;
            $transfer['hub_id'] = $request->hub_id;
            $transfer['hub_status'] = $request->hub_status;
            HubTransfer::create($transfer);
        }

        $response = [
            'msg' => 'Successfully Updated'
        ];
        return response()->json($response, 200);
    }
    
    public function hub_rider_Assign(Request $request){
        foreach ($request->courier_id as $id) {
            $hubs['rider_id'] = $request->rider_id;
            CourierRequest::where('id',$id)->update($hubs);;
        }

        $response = [
            'msg' => 'Successfully Updated'
        ];
        return response()->json($response, 200);
    }
    
    public function invoice(Request $request){
        $courierrequest = new CourierRequest();

        if($request->has('merchantname') && $request->merchantname != null){
            $courierrequest = $courierrequest->where('customer_id',$request->merchantname)->where('status_id','18')->where('invoice','0');
        }
        $courierrequest = $courierrequest->orderBy('id','desc')->paginate(200);
        

        if (isset($request->merchantname)) {
            $render['merchantname'] = $request->merchantname;
            $courierrequest = $courierrequest->appends($render);
        }

        $data['courier_requests'] = $courierrequest;
        return view('courierRequests.invoice',$data);
    }

    public function invoice_mail(Request $request){
        if (!empty($request->ids)) {
            
            $data['number'] = time().$request->merchant_id;
            $data['merchant_id'] = $request->merchant_id;
            $mercahnt = User::find($request->merchant_id);
            $data['merchant_name'] = $mercahnt->name;
            if (!empty($mercahnt->email)) {
                $data['merchant_email'] = $mercahnt->email;
            }
            if (!empty($mercahnt->phone)) {
                $data['merchant_phone'] = $mercahnt->phone;
            }
            if (!empty($mercahnt->merchant_shop_address)) {
                $data['merchant_address'] = $mercahnt->merchant_shop_address;
            }
            $data['merchant_email'] = $mercahnt->email;
            $data['reference_id'] = $request->reference_id;
            $data['added_by'] = Auth::user()->id;
            $invoice_id = Invoice::create($data)->id;

            foreach ($request->ids as $courier_id) {
                $list['invoice_id'] = $invoice_id;
                $list['courier_id'] = $courier_id;
                
                $courier = CourierRequest::find($courier_id);
                $list['tracking_id'] = $courier->tracking_id;

                if (!empty($courier->cash_on_delivery_amount)) {
                    $list['cod'] = $courier->cash_on_delivery_amount;
                }else{
                    $list['cod'] = '0';
                }

                $pricing = Pricing::find($courier->pricing_id);
                if (!empty($pricing->price)) {
                    $list['delivery_charge'] = $pricing->price;
                }else{
                    $list['delivery_charge'] = '0';
                }

                if(!empty($courier->cash_on_delivery_amount)){
                    if($courier->paid_by == "sender"){
                        $list['mercahnt_payable'] = $courier->cash_on_delivery_amount;
                    }elseif($courier->paid_by == "receiver"){
                        $list['mercahnt_payable'] = $courier->cash_on_delivery_amount;
                    }elseif($courier->paid_by == "merged_with_cod"){
                        $list['mercahnt_payable'] = $courier->cash_on_delivery_amount -  $pricing->price;
                    }
                }else{
                    if($courier->paid_by == "merged_with_cod"){
                        $list['mercahnt_payable'] = 0 -  $pricing->price;
                    }else{
                        $list['mercahnt_payable'] = 0;
                    }
                }

                $list['paid_by'] = $courier->paid_by;
                $list['request_date'] = $courier->created_at;
                $list['delivery_date'] = $courier->delivery_date;

                $list['receiver_name'] = $courier->receiver_name;
                $list['receiver_phone'] = $courier->receiver_phone;
                $list['receiver_address'] = $courier->receiver_address;
                $list['cod_payment_status'] = $courier->cod_payment_status;
                $list['status_id'] = $courier->status_id;


                InvoiceList::create($list);
                $mark['invoice'] = '1';
                CourierRequest::where('id',$courier_id)->update($mark);
            }
            

            session()->flash('message','Generate Successfully');
            return redirect()->route('dashboard.invoice.show.list',$invoice_id);
        } else {
            session()->flash('message','Not selected any checkbox');
            return redirect()->back();
        }
    }

    public function invoice_list(Request $request){
        $invoice = new Invoice();

        if($request->has('merchantname') && $request->merchantname != null){
            $invoice = $invoice->where('merchant_id',$request->merchantname);
        }
        $invoice = $invoice->orderBy('id','desc')->paginate(100);
        

        if (isset($request->merchantname)) {
            $render['merchantname'] = $request->merchantname;
            $invoice = $invoice->appends($render);
        }

        $data['invoices'] = $invoice;
        return view('courierRequests.invoice_list',$data);
    }

    public function show_invoice($id){
        $data['invoice'] = Invoice::find($id);
        $data['invoice_lists'] = InvoiceList::where('invoice_id',$id)->get();
        return view('courierRequests.invoice_show',$data);
    }
    
    public function email_invoice($id){
        $invoice = Invoice::find($id);
        $data['invoice'] = $invoice;
        $data['invoice_lists'] = InvoiceList::where('invoice_id',$id)->get();

        $send_num['send_number'] = $invoice->send_number + 1;
        Invoice::where('id',$id)->update($send_num);

        $data["email"] = $invoice->merchant_email;
        $data["title"] = "Invoice";

        $customPaper = array(0,0,1000, 1200);
        $pdf = PDF::loadView('courierRequests.email', $data)->setOptions(['defaultFont' => 'sans-serif'])->setPaper($customPaper, 'landscape');
  
        Mail::send('courierRequests.emailmessage', $data, function($message)use($data, $pdf) {
            $message->to($data["email"], $data["email"])
                    ->subject($data["title"])
                    ->attachData($pdf->output(), "invoice.pdf");
        });

        return redirect()->back();
    }
    
    public function pushnotification($tracking_id,$customer_id)
    {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $users = User::find($customer_id);
        if (!empty($users->device_id)) {
            $token= $users->device_id;
        }else{
            return true;
        }
        
        $title = "Your Payment has been completed";
        $body = "Dear ".$users->name.", Your ".$tracking_id." courier id payment has been completed.";
        
        $notification = [
            'title' => $title,
            'body' => $body,
            'sound' => true,
            'click_action'=> 'FLUTTER_NOTIFICATION_CLICK',
        ];

        $body_notification = [
            "title"=>$title,
            "body"=>$body
        ]; 


        
        $extraNotificationData = ["message" => $body_notification,"moredata" =>'dd'];

        $fcmNotification = [
            //'registration_ids' => $tokenList, //multple token array
            'to'        => $token, //single token
            'notification' => $notification,
            'data' => $body_notification
        ];

        $headers = [
            'Authorization: key=AAAAmE4-Acw:APA91bGr7v3yZj9sllJI0wO6tlJ-dWZJFkjKYn7XFKVQdopfbYiDpWwhqtYfjUcsto9JW1lJK4O-TJp8-ZJscvTM1SPiqwn-uGTtxZfJFLjTTOGksdUtvlpezXYRaw-DeSp14W3RXvUH',
            'Content-Type: application/json'
        ];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
        curl_close($ch);

        return true;
    }
    
    public function agent_request(){
        $courier_requests = \DB::table('courier_requests')->join('users','courier_requests.customer_id','users.id')->where('courier_requests.status_id','18')->where('courier_requests.cash_on_delivery_amount','>',0)->where('users.preferred_method','bkash')->select('courier_requests.id as courier_id','courier_requests.tracking_id as tracking_id','courier_requests.paid_by as paid_by','courier_requests.cash_on_delivery_amount as cash_on_delivery_amount','courier_requests.pricing_id as pricing_id','courier_requests.customer_id as customer_id','courier_requests.cod_payment_status as cod_payment_status')->get();
        $request = array();
        foreach ($courier_requests as $courier_request) {
            if ($courier_request->cod_payment_status == null || $courier_request->cod_payment_status == 'no' ) {
                $pricing = \App\Models\Pricing::find($courier_request->pricing_id);
                $charge = !empty($pricing->price)?$pricing->price:'0';

                $customers = \App\Models\User::find($courier_request->customer_id);
                $customer = !empty($customers->name)?$customers->name:"Not Available";
                

                if(!empty($courier_request->cash_on_delivery_amount)){
                    if($courier_request->paid_by == "sender"){
                        $amount = $courier_request->cash_on_delivery_amount;
                    }elseif($courier_request->paid_by == "receiver"){
                        $amount = $courier_request->cash_on_delivery_amount;
                    }elseif($courier_request->paid_by == "merged_with_cod"){
                        $amount = $courier_request->cash_on_delivery_amount -  $charge;
                    }
                }else{
                    $amount = '0';
                }

                if ($amount > '0') {
                    $courier_request->price = $amount;
                }else{
                    $courier_request->price = '0';
                }
                $courier_request->delivery_charge = $charge;
                $courier_request->tracking_id = $courier_request->tracking_id;
                $courier_request->merchant_name = $customer;
                $courier_request->phone = !empty($customers->bkash_no)?$customers->bkash_no:"Not Available";
                $request[] = (array)$courier_request; 
                }
        }

        $response = [
            'data' => $request,
        ];
        return response()->json($response, 200);

    }

    public function agent_sent_payment_request(Request $request){

        $this->validate($request,[
            'transaction_id' => 'required|unique:agent_payment_logs',
        ]);

        $courier_request = CourierRequest::findOrFail($request->courier_id);
        $data['cod_payment_status'] = $request->status;
        $data['cod_payment_status_date'] = date('Y-m-d H:i:s'); 
        $data['preferred_method'] = 'bkash';
        $data['preferred_method_number'] = $request->phone;
        CourierRequest::where('id',$request->courier_id)->update($data);
        
        $payment['courier_id'] = $request->courier_id;
        $payment['amount'] = $request->amount;
        $payment['phone'] = $request->phone;
        $payment['trackingid'] = $request->trackingid;
        $payment['transaction_id'] = $request->transaction_id;
        $payment['user_id'] = Auth::user()->id;
        \App\Models\AgentPaymentLog::create($payment);

        $this->pushnotification($courier_request->tracking_id,$courier_request->customer_id);
        $response = [
            'msg' => "Successfully Done",
        ];
        return response()->json($response, 200);
    }
    
    public function agent_paid_list(){
        $payments = \App\Models\AgentPaymentLog::get();
        
         $response = [
            'data' => $payments,
        ];
        return response()->json($response, 200);
    }
    
    public function bkash_agent_to_merchant_payable(Request $request){
        $CourierRequest = new AgentPaymentLog();
        if($request->sdate && $request->sdate != null && $request->edate && $request->edate != null){
            $CourierRequest = $CourierRequest->whereBetween('created_at',[$request->sdate, $request->edate]);
        }
        if($request->sdate && $request->sdate != null){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->sdate);
        }
        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('courier_id',$request->courierid);
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('trackingid',$request->trackingid);
        }
        
        
        $CourierRequest = $CourierRequest->orderBy('id','desc')->paginate(100);

        if (isset($request->courierid) || $request->trackingid || $request->sdate || $request->edate) {
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $CourierRequest = $CourierRequest->appends($render);
        }

        $data['courier_requests'] = $CourierRequest;

        return view('courierRequests.agent_bkash',$data);
    }
    
    public function ledger(Request $request, $id){

        $CourierRequest = new Invoice();
        if($request->has('number') && $request->number != null){
            $CourierRequest = $CourierRequest->where('number',$request->number);
        }
        if($request->has('reference_id') && $request->reference_id != null){
            $CourierRequest = $CourierRequest->where('reference_id',$request->reference_id); 
        }
        if($request->has('pickupdate') && $request->pickupdate != null){
            $CourierRequest = $CourierRequest->whereDate('created_at',$request->pickupdate);
        }
        $CourierRequest = $CourierRequest->where('merchant_id',$id)->orderBy('id','desc')->get();
        

        
        $data['courier_requests'] = $CourierRequest;
        return view('courierRequests.ledger',$data);
    }
    
    public function rider_pickup(){
        $courier_requests = CourierRequest::with('delivery_mode','packaging_type','receiver_area','sender_area','branch',
                'receiver_city','sender_city','courier_type','status','pricing','customer')->where('pickup_rider', Auth::id())->orderBy('id','desc')->paginate(20);
        $response = [
            'data' => $courier_requests,
        ];
        return response()->json($response, 200);
    }

    public function rider_delivery(){
        $courier_requests = CourierRequest::with('delivery_mode','packaging_type','receiver_area','sender_area','branch',
                'receiver_city','sender_city','courier_type','status','pricing','customer')->where('delivery_rider', Auth::id())->orderBy('id','desc')->paginate(20);
        $response = [
            'data' => $courier_requests,
        ];
        return response()->json($response, 200);
    }
    
    public function rider_commission(){
        $pickup_rider_com_due = CourierRequest::where('pickup_rider',Auth::id())->where('pickup_rider_commission_payment','no')->sum('pickup_rider_commission');
        $delivery_rider_com_due = CourierRequest::where('delivery_rider',Auth::id())->where('delivery_rider_commission_payment','no')->sum('delivery_rider_commission');
        $due = $pickup_rider_com_due + $delivery_rider_com_due;

        $pickup_rider_com_paid = CourierRequest::where('pickup_rider',Auth::id())->where('pickup_rider_commission_payment','yes')->sum('pickup_rider_commission');
        $delivery_rider_com_paid = CourierRequest::where('delivery_rider',Auth::id())->where('delivery_rider_commission_payment','yes')->sum('delivery_rider_commission');
        $paid = $pickup_rider_com_paid + $delivery_rider_com_paid;
        $response = [
            'pickup_due' => $pickup_rider_com_due,
            'delivery_due' => $delivery_rider_com_due,
            'total_due' => $due,
            'pickup_paid' => $pickup_rider_com_paid,
            'delivery_paid' => $delivery_rider_com_paid,
            'total_paid' => $paid,
        ];
        return response()->json($response, 200);
    }

    public function agent_commission(){
        $pickup_agent_com_due = CourierRequest::where('branch_id',Auth::user()->hub_id)->where('pickup_agent_id',Auth::id())->where('pickup_agent_commission_payment','no')->sum('pickup_agent_commission');
        $delivery_agent_com_due = CourierRequest::where('delivery_hub',Auth::user()->hub_id)->where('delivery_agent_id',Auth::id())->where('delivery_agent_commission_payment','no')->sum('delivery_agent_commission');
        $due = $pickup_agent_com_due + $delivery_agent_com_due;
        $pickup_agent_com_paid = CourierRequest::where('branch_id',Auth::user()->hub_id)->where('pickup_agent_id',Auth::id())->where('pickup_agent_commission_payment','yes')->sum('pickup_agent_commission');
        $delivery_agent_com_paid = CourierRequest::where('delivery_hub',Auth::user()->hub_id)->where('delivery_agent_id',Auth::id())->where('delivery_agent_commission_payment','yes')->sum('delivery_agent_commission');
        $paid = $pickup_agent_com_paid + $delivery_agent_com_paid;
        $response = [
            'pickup_due' => $pickup_agent_com_due,
            'delivery_due' => $delivery_agent_com_due,
            'total_due' => $due,
            'pickup_paid' => $pickup_agent_com_paid,
            'delivery_paid' => $delivery_agent_com_paid,
            'total_paid' => $paid,
        ];
        return response()->json($response, 200);
    }

    public function rider_status_change(Request $request){
        $validated = $request->validate([
            'status_id' => 'integer|required|exists:statuses,id',
            'courier_id.*' => 'integer|required'
        ]);
        foreach ($request->courier_id as $courier_id) {
            $status = Status::findOrFail($validated['status_id']);

            if(!$status){
                return BlendxHelpers::generate_response(true, "Status with id: ".$validated['status_id']." not found", []);
            }

            $courier_request = CourierRequest::findOrFail($courier_id);
            $data['status_id'] = $request->status_id;
            if ($request->status_id == "18"){
                $data['delivery_date'] = date('Y-m-d H:i:s');
            }
            CourierRequest::where('id',$courier_id)->update($data);
             
            $stat = Status::find($request->status_id);
            $courier['courier_id'] = $courier_id;
            $courier['name'] = $stat->name;
            $courier['sequence'] = $stat->sequence;;
            $courier['status_id'] = $request->status_id;
            $courier['note'] = $request->note;
            $courier['user_id'] = Auth::user()->id;
            CourierRequestLog::create($courier);

            if ($request->status_id == "13") {
                $courier = CourierRequest::find($courier_id);
                $picks = User::find($courier->pickup_rider);

                $rider['courier_id'] = $courier_id;
                $rider['rider_id'] = $courier->pickup_rider;
                $rider['type'] = 'pickup_rider';
                $rider['amount'] = !empty($picks->pickup_rider_commission)?$picks->pickup_rider_commission:'0';
                $rider['addedby'] = Auth::user()->id;
                \App\Models\RiderCommission::create($rider);
 
                $branch = Branch::find($courier->branch_id);
                if ($branch->is_agent == '1') {
                    $agent_picks = User::where('hub_id',$courier->branch_id)->first();
                    $agent['courier_id'] = $courier_id;
                    $agent['agent_id'] = $agent_picks->id;
                    $agent['type'] = 'pickup_agent';
                    $per = !empty($agent_picks->pickup_agent_commission)?$agent_picks->pickup_agent_commission:'0';
                    $agent['percentage'] = $per;

                    $price = Pricing::find($courier->pricing_id)->price;
                    $agent['charge'] = $price;
                    $amounts = ($price*$per)/100;
                    $agent['amount'] = $amounts;
                    $agent['addedby'] = Auth::user()->id;
                    \App\Models\AgentCommission::create($agent);
                }else{
                    $amount = '0';
                }

                $datas['pickup_rider_commission'] = !empty($picks->pickup_rider_commission)?$picks->pickup_rider_commission:'0';
                $datas['pickup_rider_commission_added'] = Auth::user()->id;
                $datas['pickup_agent_commission'] = $amounts;
                $datas['pickup_agent_id'] = $agent_picks->id;
                $datas['pickup_agent_commission_added'] = Auth::user()->id;
                $datas['pickup_date'] = date('Y-m-d H:i:s');
                CourierRequest::where('id',$courier_id)->update($datas);
                
            }

            if ($request->status_id == "17") {
                if (!empty($request->note)) {
                    $courier_request = CourierRequest::findOrFail($courier_id);
                    $text = "Tracking ID ".$courier_request->tracking_id.':'.$request->note;
                    $to = User::find($courier_request->customer_id)->phone;
                    $this->sms($to, $text);
                }
                
            }

            if ($request->status_id == "16") {
                $courier_request = CourierRequest::findOrFail($courier_id);
                $text = "Dear ". $courier_request->receiver_name.",Your parcel is on the way to Deliver";
                $to = $courier_request->receiver_phone;
                $this->sms($to, $text);

                $courier = CourierRequest::find($courier_id);
                $picks = User::find($courier->delivery_rider);
                $rider['courier_id'] = $courier_id;
                $rider['rider_id'] = $courier->delivery_rider;
                $rider['type'] = 'delivery_rider';
                $rider['amount'] = !empty($picks->delivery_rider_commission)?$picks->delivery_rider_commission:'0';
                $rider['addedby'] = Auth::user()->id;
                \App\Models\RiderCommission::create($rider);

                
                $branch = Branch::find($courier->branch_id);
                if ($branch->is_agent == '1') {
                    $agent_picks = User::where('hub_id',$courier->branch_id)->first();
                    $agent['courier_id'] = $courier_id;
                    $agent['agent_id'] = $agent_picks->id;
                    $agent['type'] = 'delivery_agent';
                    $per = !empty($agent_picks->delivery_agent_commission)?$agent_picks->delivery_agent_commission:'0';
                    $agent['percentage'] = $per;

                    $price = Pricing::find($courier->pricing_id)->price;
                    $agent['charge'] = $price;
                    $amounts = ($price*$per)/100;
                    $agent['amount'] = $amounts;
                    $agent['addedby'] = Auth::user()->id;
                    \App\Models\AgentCommission::create($agent);
                }else{
                    $amount = '0';
                }

                $datas['delivery_rider_commission'] = !empty($picks->delivery_rider_commission)?$picks->delivery_rider_commission:'0';
                $datas['delivery_rider_commission_added'] = Auth::user()->id;
                $datas['delivery_agent_commission'] = $amounts;
                $datas['delivery_agent_id'] = $agent_picks->id;
                $datas['delivery_agent_commission_added'] = Auth::user()->id;
                $datas['assign_date'] = date('Y-m-d H:i:s');
                CourierRequest::where('id',$courier_id)->update($datas);
            }
        }
        return response()->json(BlendxHelpers::generate_response(false, 'Status updated!', []));
    }
    
    public function pickup_rider_courier(Request $request,$id){

        $CourierRequest = new CourierRequest();

        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('id',$request->courierid);
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid); 
        }
        
        if($request->has('pickupdate') && $request->pickupdate != null){
            $CourierRequest = $CourierRequest->whereDate('pickup_date',$request->pickupdate);
        }
        $CourierRequest = $CourierRequest->where('pickup_rider',$id)->orderBy('id','desc')->paginate(30);
        

        if (isset($request->pickupdate) || $request->courierid || $request->trackingid) {
            $render['pickupdate'] = $request->pickupdate;
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $CourierRequest = $CourierRequest->appends($render);
        }

        $data['courier_requests'] = $CourierRequest;
        return view('courierRequests.pickupRiderList',$data);
    }

    public function pickup_rider_payment_courier(Request $request){
        if (!empty($request->ids)) {
            foreach ($request->ids as $ide) {
                
                $data['pickup_rider_commission_payment'] = $request->pickup_rider_commission_payment;
                $data['pickup_rider_commission_payment_date'] = date('Y-m-d H:i:s');
                CourierRequest::where('id',$ide)->update($data);
                
            }
            session()->flash('message','Update Successfully');
            return redirect()->back();
        } else {
            session()->flash('message','Not selected any checkbox');
            return redirect()->back();
        }
    }
    
    public function delivery_rider_courier(Request $request,$id){

        $CourierRequest = new CourierRequest();

        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('id',$request->courierid);
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid); 
        }
        
        if($request->has('pickupdate') && $request->pickupdate != null){
            $CourierRequest = $CourierRequest->whereDate('assign_date',$request->pickupdate);
        }
        $CourierRequest = $CourierRequest->where('delivery_rider',$id)->orderBy('id','desc')->paginate(30);
        

        if (isset($request->pickupdate) || $request->courierid || $request->trackingid) {
            $render['pickupdate'] = $request->pickupdate;
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $CourierRequest = $CourierRequest->appends($render);
        }

        $data['courier_requests'] = $CourierRequest;
        return view('courierRequests.deliveryRiderList',$data);
    }

    public function delivery_rider_payment_courier(Request $request){
        if (!empty($request->ids)) {
            foreach ($request->ids as $ide) {
                
                $data['delivery_rider_commission_payment'] = $request->delivery_rider_commission_payment;
                $data['delivery_rider_commission_payment_date'] = date('Y-m-d H:i:s');
                CourierRequest::where('id',$ide)->update($data);
                
            }
            session()->flash('message','Update Successfully');
            return redirect()->back();
        } else {
            session()->flash('message','Not selected any checkbox');
            return redirect()->back();
        }
    }
    
    public function pickup_agent_courier(Request $request,$id){
        $user = User::find($id);
        $CourierRequest = new CourierRequest();

        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('id',$request->courierid);
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid); 
        }
        
        if($request->has('pickupdate') && $request->pickupdate != null){
            $CourierRequest = $CourierRequest->whereDate('pickup_date',$request->pickupdate);
        }
        $CourierRequest = $CourierRequest->where('branch_id',$user->hub_id)->where('pickup_agent_id',$id)->where('pickup_agent_commission','>','0')->orderBy('id','desc')->paginate(30);
        

        if (isset($request->pickupdate) || $request->courierid || $request->trackingid) {
            $render['pickupdate'] = $request->pickupdate;
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $CourierRequest = $CourierRequest->appends($render);
        }

        $data['courier_requests'] = $CourierRequest;
        return view('courierRequests.pickupAgentList',$data);
    }

    public function pickup_agent_payment_courier(Request $request){
        if (!empty($request->ids)) {
            foreach ($request->ids as $ide) {
                
                $data['pickup_agent_commission_payment'] = $request->pickup_agent_commission_payment;
                $data['pickup_agent_commission_payment_date'] = date('Y-m-d H:i:s');
                CourierRequest::where('id',$ide)->update($data);
                
            }
            session()->flash('message','Update Successfully');
            return redirect()->back();
        } else {
            session()->flash('message','Not selected any checkbox');
            return redirect()->back();
        }
    }
    
    public function delivery_agent_courier(Request $request,$id){
        $user = User::find($id);
        $CourierRequest = new CourierRequest();

        if($request->has('courierid') && $request->courierid != null){
            $CourierRequest = $CourierRequest->where('id',$request->courierid);
        }
        if($request->has('trackingid') && $request->trackingid != null){
            $CourierRequest = $CourierRequest->where('tracking_id',$request->trackingid); 
        }
        
        if($request->has('pickupdate') && $request->pickupdate != null){
            $CourierRequest = $CourierRequest->whereDate('assign_date',$request->pickupdate);
        }
        $CourierRequest = $CourierRequest->where('delivery_hub',$user->hub_id)->where('delivery_agent_id',$id)->where('delivery_agent_commission','>','0')->orderBy('id','desc')->paginate(30);
        

        if (isset($request->pickupdate) || $request->courierid || $request->trackingid) {
            $render['pickupdate'] = $request->pickupdate;
            $render['courierid'] = $request->courierid;
            $render['trackingid'] = $request->trackingid;
            $CourierRequest = $CourierRequest->appends($render);
        }

        $data['courier_requests'] = $CourierRequest;
        return view('courierRequests.deliveryAgentList',$data);
    }

    public function delivery_agent_payment_courier(Request $request){
        if (!empty($request->ids)) {
            foreach ($request->ids as $ide) {
                
                $data['delivery_agent_commission_payment'] = $request->delivery_agent_commission_payment;
                $data['delivery_agent_commission_payment_date'] = date('Y-m-d H:i:s');
                CourierRequest::where('id',$ide)->update($data);
                
            }
            session()->flash('message','Update Successfully');
            return redirect()->back();
        } else {
            session()->flash('message','Not selected any checkbox');
            return redirect()->back();
        }
    }

}
