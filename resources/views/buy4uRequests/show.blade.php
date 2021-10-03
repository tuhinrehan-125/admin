@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body" style="padding: 5em;">
            <h3 class="card-title text-center mb-2">Buy4u Request ID: <span>#{{$buy4u_request->id}}</span></h3>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Buy4u Type:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$buy4u_request->buy4u_type->title}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">City:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$buy4u_request->city->name}}</p>
                </div> 
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Area:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$buy4u_request->area->name}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Customer Name:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$buy4u_request->customer->name}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Rider Name:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$buy4u_request->rider ? $buy4u_request->rider->name : "Not Assigned"}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Hub Name:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$buy4u_request->branch->name}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Address:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$buy4u_request->address}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Preferred Shop Name:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$buy4u_request->preferred_shop_name}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Preferred Shop Address:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$buy4u_request->preferred_shop_address}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Note:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$buy4u_request->note?$buy4u_request->note:'not available'}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Pricing:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$buy4u_request->pricing->price}}</p>
                </div>
            </div>
            <div class="row" style="margin: 0px;">
                <div class="col-2">
                    <p style="font-weight: 500;">Status:</p>
                </div>
                <div class="col-2">
                    <p class="">{{$buy4u_request->status->name}}</p>
                </div>
            </div>
            <br>
            @if(isset($buy4u_request->products))
                @if(count($buy4u_request->products)>0)
                <div class="row" style="margin: 0px;">
                    <div class="col-2">
                        <p style="font-weight: 500;">Products:</p>
                    </div>
                </div>
                <br>
                @endif
               @foreach($buy4u_request->products as $product)
                <div class="row" style="margin: 0px;">
                    <div class="col-2">
                        <p style="font-weight: 500;">Name:</p>
                    </div>
                    <div class="col-2">
                        <p class="">{{$product->name}}</p>
                    </div>
                </div>
                <div class="row" style="margin: 0px;">
                    <div class="col-2">
                        <p style="font-weight: 500;">Quantity:</p>
                    </div>
                    <div class="col-2">
                        <p class="">{{$product->quantity}}</p>
                    </div>
                </div>
                <div class="row" style="margin: 0px;">
                    <div class="col-2">
                        <p style="font-weight: 500;">Unit Name:</p>
                    </div>
                    <div class="col-2">
                        <p class="">{{\App\Models\UnitType::find($product->unit_type_id)->title}}</p>
                    </div>
                </div>
                <div class="row" style="margin: 0px;">
                    <div class="col-2">
                        <p style="font-weight: 500;">Approximate Price:</p>
                    </div>
                    <div class="col-2">
                        <p class="">{{$product->approximate_price}}</p>
                    </div>
                </div>
                @if(isset($product->note))
                    <div class="row" style="margin: 0px;">
                        <div class="col-2">
                            <p style="font-weight: 500;">Note:</p>
                        </div>
                        <div class="col-2">
                            <p class="">{{$product->note}}</p>
                        </div>
                    </div>
                @endif
                <br>
                @endforeach
            @endif
        </div>
    </div>
@endsection
