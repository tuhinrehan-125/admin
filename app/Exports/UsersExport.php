<?php

namespace App\Exports;

use App\Models\CourierRequest;
use Maatwebsite\Excel\Concerns\FromCollection;

class UsersExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection() 
    {		
    $courier_requests = CourierRequest::get();
     $courier_array[] = array('Courier Id', 'Courier Tracking Id','Courier Type','Merchant name', 'Receiver Info','COD Amount','Delivery Charge','Status','COD Payment','Paid By','Time','Time Duration');
     foreach($courier_requests as $courier_request){
      $courier_array[] = array(
       'Courier Id'  => $courier_request->id,
       'Courier Tracking Id'   => $courier_request->tracking_id,
       'Courier Type'   => $courier_request->courier_type->title,
       'Merchant name'    => !empty($courier_request->customer->name)?$courier_request->customer->name:"Not Available",
       'Receiver Info'  => $courier_request->receiver_name.'-- '. $courier_request->receiver_address .'-- '.$courier_request->receiver_phone,
       'COD Amount'   => $courier_request->cash_on_delivery_amount,
       'Delivery Charge'   => $courier_request->pricing->price,
       'Status'   => $courier_request->status->name,
       'COD Payment'   => $courier_request->cod_payment_status,
       'Paid By'   => $courier_request->paid_by,
       'Time'   => date('d-m-Y h:i A',strtotime($courier_request->created_at)),
       'Time Duration'   => $courier_request->created_at->diffForHumans()
      );

     }

        return collect($courier_array);	 
    }
}
