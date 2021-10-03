@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body" style="padding: 5em;">
            <h3 class="card-title text-center mb-2">Courier Request ID: <span>#{{$courier_request->id}}</span></h3>
            <h3 class="card-title text-center mb-2">Courier Tracking ID: <span>#{{$courier_request->tracking_id}}</span></h3>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Courier Time:</p>
                </div>
                <div class="col-2">
                    <p class="">{{ date('d M,Y h:i A',strtotime($courier_request->created_at)) }}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Courier Type:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->courier_type->title}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Sender City:</p> 
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->sender_city->name}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Receiver City:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->receiver_city->name}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Sender Area:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->sender_area->name}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Receiver Area:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->receiver_area->name}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Merchant Name:</p>
                </div>
                <div class="col-2">
                    <p class="">{{ !empty($courier_request->customer->name)?$courier_request->customer->name:'Not Available' }}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Customer Phone:</p>
                </div>
                <div class="col-2">
                    <p class="">{{(!empty($courier_request->customer->phone)) ? $courier_request->customer->phone:'Not Available'}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Preferred Method:</p>
                </div>
                <div class="col-2">
                    <p class="">{{(!empty($courier_request->customer->preferred_method)) ? $courier_request->customer->preferred_method:'Not Available'}}</p>
                </div>
            </div>
            
            
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Rider Name:</p>
                </div>
                <div class="col-2">
                    <p class="">{{(!empty($courier_request->rider)) ? $courier_request->rider->name : 'Not Assigned'}}</p>
                </div>
            </div>
            

            @php
                $pickups = \App\Models\User::find($courier_request->pickup_rider);
                $deliveries = \App\Models\User::find($courier_request->delivery_rider);
            @endphp
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Pickup Rider:</p>
                </div>
                <div class="col-2">
                    <p class="">{{(!empty($pickups->name)) ? $pickups->name : 'Not Assigned'}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Delivery Rider:</p>
                </div>
                <div class="col-2">
                    <p class="">{{(!empty($deliveries->name)) ? $deliveries->name : 'Not Assigned'}}</p>
                </div>
            </div>
            
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Hub Name:</p>
                </div>
                <div class="col-2"> 
                    <p class="">
                        @if(!empty($courier_request->branch_id))
                            @if(!empty($courier_request->branch->name))
                                {{$courier_request->branch->name}}
                            @else
                               <spna style="color: red;font-weight: 600">Not Assign</spna>
                            @endif
                        @else
                            <spna style="color: red;font-weight: 600">Not Assign</spna>
                        @endif
                    </p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Sender Address:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->sender_address}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Receiver Address:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->receiver_address}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Receiver Name:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->receiver_name}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Receiver Phone:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->receiver_phone}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Packaging Type:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->packaging_type->title}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Note:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->note}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Delivery Mode:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->delivery_mode->title}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Fragile</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->fragile?'yes':'no'}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Paid By:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->paid_by}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Cash On Delivery:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->cash_on_delivery?'yes':'no'}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Cash On Delivery Amount:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->cash_on_delivery_amount}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Approximate Weight:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->approximate_weight}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Actual Weight:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->actual_weight}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Pricing:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->pricing->price}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Status:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->status->name}}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
