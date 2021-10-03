<?php

namespace App\Imports;

use App\Models\CourierRequest;
use App\Models\CourierRequestLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Facades\Auth;
use App\Models\HubArea;
use App\Models\Branch;
use App\Models\Status;
use App\Models\City;
use App\Models\Area;
use App\Models\CourierType;
use App\Models\DeliveryMode; 
use App\Models\Pricing; 
use App\Models\PackagingType;  
use App\Models\User;  

class BulkImport implements ToCollection , WithMultipleSheets, WithCalculatedFormulas
{
    public function sheets(): array
    {
        return [
            0 => $this, 
        ];
    }
    /**
    * @param Collection $collection 
    */
    public function collection(Collection $collection)
    {
        $data = array_slice($collection->toArray(),1);
        foreach ($data as $row){ 
            if($row[0] == null){
                return redirect()->route('dashboard.courier.request');
            }
            $mercahnt_id = $row[12];
            
            $mercahnt = User::find($mercahnt_id);
            $bulk_request['customer_id'] = $mercahnt_id; 
            
            $sender_city_id = $mercahnt->merchant_shop_city;
            $sender_area_id = $mercahnt->merchant_shop_area;
            $bulk_request['sender_name'] = $mercahnt->name;
            $bulk_request['sender_phone'] = $mercahnt->phone;
            $bulk_request['sender_address'] =$mercahnt->merchant_shop_address;

            if ($sender_city_id != null) {
                $bulk_request['sender_city_id'] = $sender_city_id;
            } else {
                return redirect(route('dashboard.courier.request'))->with('message',"Merchant City Not Assign");
            }

            if ($sender_area_id != null) {
                $bulk_request['sender_area_id'] = $sender_area_id;
            } else {
                 return redirect(route('dashboard.courier.request'))->with('message',"Merchant Area Not Assign");
            }

            $receiver_city = $row[2];
            if (!empty($row[2]) || $row[2] != null) {
                $rcity_id = City::where('slug',$receiver_city)->first()->id;
                $bulk_request['receiver_city_id'] = $rcity_id;
            }else{
                return redirect(route('dashboard.courier.request'))->with('message',"City Can not be blank");
            }
            
            if (!empty($row[3]) || $row[3] != null) {
                $reciever_area = $row[3];
                $rarea_id = Area::where('city_id',$rcity_id)->where('slug',$reciever_area)->first()?Area::where('city_id',$rcity_id)->where('slug',$reciever_area)->first()->id:Area::where('slug',$reciever_area)->first()->id;
                $bulk_request['receiver_area_id'] = $rarea_id;
            }else{
                return redirect(route('dashboard.courier.request'))->with('message',"Area Can not be blank");
            }

            $deliveryhub = HubArea::where('area_id', $rarea_id)->first();
            $bulk_request['delivery_hub'] = $deliveryhub ? $deliveryhub->hub_id : 21;

           
            $courier_type_id = $this->getCouriertype($sender_city_id,$rcity_id);
            $bulk_request['courier_type_id'] = $courier_type_id;
                
            if ($courier_type_id == '3' || $courier_type_id == '8') {
                $deliver_mode_title = $row[8];
                if (!empty($row[8]) || $row[8] != null) {
                    if ($courier_type_id == '3') {
                        $delivery_mode = DeliveryMode::where('title',$deliver_mode_title)->where('courier_type_id',3)->first()?DeliveryMode::where('title',$deliver_mode_title)->first()->id:35;
                    } else{
                        $delivery_mode = DeliveryMode::where('title',$deliver_mode_title)->where('courier_type_id',8)->first()?DeliveryMode::where('title',$deliver_mode_title)->first()->id:36;
                    }
                }else{
                    return redirect(route('dashboard.courier.request'))->with('message',"Courier Type Can not be blank");
                }
            } else {
                if ($courier_type_id == '5') {
                    $delivery_mode = 31;
                }elseif ($courier_type_id == '9') {
                    $delivery_mode = 32;
                }else {
                    $delivery_mode = 33;
                }
            }
            $bulk_request['delivery_mode_id'] = $delivery_mode;

            $weight = $row[6];
            if (!empty($weight)) {
                if ($mercahnt->speical == 1) {
                    $price_id = !empty(Pricing::where('user_id',$mercahnt_id)->where('courier_type_id',$courier_type_id)->where('delivery_mode_id',$delivery_mode)->orderBy('id','desc')->where('min_weight','<',$weight)->where('max_weight', '>=',$weight)->first()->id)?Pricing::where('user_id',$mercahnt_id)->where('courier_type_id',$courier_type_id)->where('delivery_mode_id',$delivery_mode)->orderBy('id','desc')->where('min_weight','<',$weight)->where('max_weight', '>=',$weight)->first()->id:Pricing::where('user_id','0')->where('courier_type_id',$courier_type_id)->where('delivery_mode_id',$delivery_mode)->orderBy('id','desc')->where('min_weight','<',$weight)->where('max_weight', '>=',$weight)->first()->id;
                    $a_weight = $weight;
                }else{
                    $price_id = Pricing::where('user_id','0')->where('courier_type_id',$courier_type_id)->where('delivery_mode_id',$delivery_mode)->orderBy('id','desc')->where('min_weight','<',$weight)->where('max_weight', '>=',$weight)->first()->id;
                    $a_weight = $weight;
                }
                

            }else{
                $price_id = Pricing::where('user_id','0')->where('courier_type_id',$courier_type_id)->where('delivery_mode_id',$delivery_mode)->orderBy('id','asc')->first()->id;
                $a_weight = Pricing::where('user_id','0')->where('courier_type_id',$courier_type_id)->where('delivery_mode_id',$delivery_mode)->orderBy('id','asc')->first()->max_weight;
            }

            $bulk_request['pricing_id'] = $price_id;
            $bulk_request['approximate_weight'] = $a_weight;
            $bulk_request['actual_weight'] = $a_weight;
            

            $branch = HubArea::where('area_id', $sender_area_id)->first();
            $bulk_request['branch_id'] = $branch ? $branch->hub_id : null;         

            $bulk_request['receiver_name'] = $row[0];
            $bulk_request['receiver_phone'] = $row[1];
            $bulk_request['receiver_address'] = $row[4];

            
            $package_id = $row[5];
            if (!empty($package_id) || $package_id =! null){
                $packaging_type_id = PackagingType::where('title',$package_id)->first()->id;
            }else {
                $packaging_type_id = 8;
            }
            $bulk_request['packaging_type_id'] = $packaging_type_id;
                
            $frag = $row[7];
            if ($frag == 'yes'){
                $fragile = 1;
            }else {
                $fragile = 0;
            }
            $bulk_request['fragile'] = $fragile;
            $bulk_request['paid_by'] = $row[9];

            $amount = $row[10];
            if ($amount > 0) {
                $cash_on_delivery_amount = $amount;
                $cash_on_delivery = 1;
                $bulk_request['cash_on_delivery_amount'] = $cash_on_delivery_amount;
            } else {
                 $cash_on_delivery = 0;
            }
            $bulk_request['cash_on_delivery'] = $cash_on_delivery; 
                
            $bulk_request['note'] = $row[11];

            $status = Status::where('sequence', 0)->first();
            $bulk_request['status_id'] = $status ? $status->id : 0;

            $bulk_request['bulk'] = 1;

            $courier_request = CourierRequest::create($bulk_request);

            $currie['tracking_id'] = time().''.$courier_request->id.'b';
            CourierRequest::where('id', $courier_request->id)->update($currie);

            $status = Status::where('sequence', 0)->first();
            $courier['courier_id'] = $courier_request->id;
            $courier['name'] = $status->name;
            $courier['sequence'] = $status->sequence;
            $courier['status_id'] = $status->id;
            CourierRequestLog::create($courier);
                
        }
    }

    public function getCouriertype($city_id,$rcity_id){
        $sender_city = City::find($city_id);
        $receiver_city = City::find($rcity_id);
        if (($sender_city->name == "Dhaka") && ($receiver_city->name == "Dhaka")){
            return CourierType::where('id',3)->first()->id;
        } elseif (($sender_city->name == "Chittagong") && ($receiver_city->name == "Chittagong")) {
            return CourierType::where('id',8)->first()->id;
        } elseif (($sender_city->name == "Chittagong") && ($receiver_city->name == "Dhaka") || ($sender_city->name == "Dhaka") && ($receiver_city->name == "Chittagong")) {
            return CourierType::where('id',5)->first()->id;
        }elseif ( (($sender_city->name == "Dhaka") && (($receiver_city->name == "Ashulia") || ($receiver_city->name == "Gazipur")|| ($receiver_city->name == "Savar") || ($receiver_city->name == "Narayanganj") || ($receiver_city->name == "Keraniganj"))) ||  ( (($sender_city->name == "Ashulia") || ($sender_city->name == "Gazipur")|| ($sender_city->name == "Savar") || ($sender_city->name == "Narayanganj") || ($sender_city->name == "Keraniganj")) && ($receiver_city->name == "Dhaka") ) ) {
            return CourierType::where('id',9)->first()->id;
        } else {
            return CourierType::where('id',13)->first()->id;
        }

    }
}
