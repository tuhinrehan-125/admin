@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body" style="padding: 5em;">
            <h3 class="card-title text-center mb-2">Courier Request ID: <span>#{{$courier_request->id}}</span></h3>
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
                    <p style="font-weight: 500;">Customer Name:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->customer->name}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Rider Name:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$courier_request->rider->name}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Branch Name:</p>
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
