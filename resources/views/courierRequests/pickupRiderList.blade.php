@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
         <form> 
            <div class="col-md-12">
                <div class="row filter-row"> 
                    <div class="col-sm-6 col-md-3"> 
                        <div class="form-group form-focus">
                            <label class="focus-label">Courier ID</label>
                            <input type="text" class="form-control " name="courierid" value="{{ request()->courierid }}">
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="form-group form-focus"> 
                            <label class="focus-label">Tracking ID</label>
                            <input type="text" class="form-control " name="trackingid" value="{{ request()->trackingid }}">
                        </div> 
                    </div> 
                    
                    <div class="col-sm-6 col-md-3">
                        <div class="form-group form-focus">
                            <label class="focus-label">Pickup Date</label>
                            <input type="date" value="{{ request()->pickupdate }}" class="form-control input-medium search-query" name="pickupdate" onkeypress="return true">
                        </div>
                    </div>
                    
                    <div class="col-sm-6 col-md-3">
                        <label class="focus-label"></label>
                        <button class="btn btn-success btn-block"> Search </button>
                    </div>
                </div>
            </div>
        </form>

        <form action="{{ route('dashboard.courier.rider.payment.request') }}" method="post">
        @csrf 
        <div class="card-header">
          <div class="panel-heading">
            <div class="row"> 
              <div class="col-md-4">
                <h3 class="card-title">  
                <strong> Payment  </strong>
                </h3>   
              </div> 

                <div class="col-md-4">
                    <select name="pickup_rider_commission_payment" class="form-control">
                        <option value="">Select Payment Complete</option>
                        <option value="yes">Yes</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                   <button type="submit" class="btn btn-info" style="margin-right: 10px">@lang('Submit')</button>
                </div>

            </div> 
          </div>
        </div>
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Pickup RiderCourier Requests Table</p>
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif

            <div class="table-responsive">
                <table id="example" class="display table table-bordered" style="width:100%">
                    <thead> 
                    <tr>
                        <th><input type="checkbox" class="select-all"/></th>
                        <th scope="col">ID</th>
                        <th scope="col">Type</th>
                        <th scope="col">Merchant name</th>
                        <th scope="col">Receiver Info</th> 
                        <th scope="col">Courier Amount</th>
                        <th scope="col">Status</th> 
                        <th scope="col">Rider Amount</th>

                        <th scope="col">Time</th> 
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($courier_requests as $courier_request)

                            <tr @if($courier_request->pickup_rider_commission_payment == "yes") style="background: #8fc38f;" @endif>
                                <td> <input type="checkbox" name="ids[]" value="{{$courier_request->id}}" @if($courier_request->pickup_rider_commission_payment == "yes") disabled @endif></td>
                                <td>
                                    Courier ID : {{$courier_request->id}}<br>
                                    Tracking ID : {{$courier_request->tracking_id}}
                                </td>

                                <td>
                                    Courier Type : {{ !empty($courier_request->courier_type->title)?$courier_request->courier_type->title:"Not Available" }} <br>
                                    Delivery Mode : {{ !empty($courier_request->delivery_mode->title)?$courier_request->delivery_mode->title:"Not Available" }}
                                </td>
                                <td>{{(!empty($courier_request->customer->name) ? $courier_request->customer->name : 'Not Available')}}</td>
                                <td>
                                    {{$courier_request->receiver_name}}<br>
                                    {{$courier_request->receiver_address}}<br>
                                    {{$courier_request->receiver_phone}}
                                </td>

                                <td>
                                    COD Amount : {{!empty($courier_request->cash_on_delivery_amount)?$courier_request->cash_on_delivery_amount:0}}<br>
                                    Delivery Charge : {{ !empty($courier_request->pricing->price)?$courier_request->pricing->price:'0' }}<br>
                                        @if(!empty($courier_request->cash_on_delivery_amount))
                                            @if($courier_request->paid_by == "sender")
                                               Collectable :  {{ $courier_request->cash_on_delivery_amount }}
                                            @elseif($courier_request->paid_by == "receiver")
                                               Collectable :  {{ $courier_request->cash_on_delivery_amount +  $courier_request->pricing->price}}
                                            @elseif($courier_request->paid_by == "merged_with_cod")
                                               Collectable :  {{ $courier_request->cash_on_delivery_amount }}
                                            @endif
                                        @else
                                            @if($courier_request->paid_by == "merged_with_cod" || $courier_request->paid_by == "sender")
                                               Collectable :  0
                                            @else
                                                Collectable : {{$courier_request->pricing->price}}
                                            @endif
                                        @endif
                                        <br>

                                        @if(!empty($courier_request->cash_on_delivery_amount))
                                            @if($courier_request->paid_by == "sender")
                                                Mercahnt Payable : {{ $courier_request->cash_on_delivery_amount }}
                                            @elseif($courier_request->paid_by == "receiver")
                                                Mercahnt Payable : {{ $courier_request->cash_on_delivery_amount}}
                                            @elseif($courier_request->paid_by == "merged_with_cod")
                                                Mercahnt Payable : {{ $courier_request->cash_on_delivery_amount -  $courier_request->pricing->price}}
                                            @endif
                                        @else
                                            @if($courier_request->paid_by == "merged_with_cod")
                                                Mercahnt Payable : {{ 0 -  $courier_request->pricing->price}}
                                            @else
                                                Mercahnt Payable : 0
                                            @endif
                                        @endif
                                </td>
                                <td>
                                    Status : {{!empty($courier_request->status->name)?$courier_request->status->name:"Not Available"}}<br>
                                    Paid By : {{ $courier_request->paid_by }}
                                </td>
                                <td>
                                    Commission Amount: {{!empty($courier_request->pickup_rider_commission)?$courier_request->pickup_rider_commission:"0"}} <br>
                                    @if($courier_request->pickup_rider_commission > 0)
                                        Commission Paid : {{!empty($courier_request->pickup_rider_commission_payment)?$courier_request->pickup_rider_commission_payment:"Not Available"}}
                                    @endif
                                </td>
                                
                                <td>
                                    Request Date : {{ date('d-m-Y h:i A',strtotime($courier_request->created_at)) }}<br>
                                    @php
                                    $picks =\App\Models\CourierRequestLog::where('courier_id',$courier_request->id)->where('status_id','13')->first();
                                    @endphp
                                    Pickup Date :{{ !empty($picks)? date('d-m-Y h:i A',strtotime($picks->created_at)) :"Not Available" }}<br>
                                    Delivery Date: {{ !empty($courier_request->delivery_date)?date('d-m-Y h:i A',strtotime($courier_request->delivery_date)):"Not Available" }}
                                </td>
                                
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
                {{ $courier_requests->links() }}
    </form>


@endsection
@push('custom-css')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
<link href="https://administration.holisterbd.com/public/assets/css/bootstrap-toggle.min.css" rel="stylesheet">

<style>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>



@endpush
@push('scripts')

    
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>
<script src="https://administration.holisterbd.com/public/assets/js/bootstrap-toggle.min.js"></script>

<script>
/*    $(document).ready(function() {
    $('#example').DataTable( {
        pageLength: 10,
        
        lengthMenu: [ [10, 25, 50,100,500], [10, 25, 50,100,500] ],
        dom: 'lBf',
        buttons: [
            'excel'
        ]
    } );
} );*/
</script>

<script type="text/javascript">
$(document).ready(function() {
        $('.select-all').on('click', function() {
            var checkAll = this.checked;
            $('input[type=checkbox]').each(function() {
                this.checked = checkAll;
            });
        });
    });
</script>



@endpush
