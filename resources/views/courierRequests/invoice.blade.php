@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
         <form> 
            <div class="col-md-12">
                <div class="row filter-row"> 
                    
                    <div class="col-sm-6 col-md-3">
                        <div class="form-group form-focus">
                            <label class="focus-label">Merchant Name</label>
                            <select class="select select2-hidden-accessible form-control" data-select2-id="1" tabindex="-1" aria-hidden="true" name="merchantname">
                                <option value="">Select Merchant Name</option>
                                    @foreach($users=\App\Models\User::where('type','merchant')->orwhere('type','individual')->select('id','name')->orderBy('name')->get() as $user)
                                        <option @if(request()->merchantname == $user->id) selected @endif value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                            </select>
                        </div> 
                    </div>
                    
                    <div class="col-sm-6 col-md-2">
                        <label class="focus-label"></label>
                        <button class="btn btn-success btn-block"> Search </button>
                    </div>
                </div> 
            </div>
        </form>
        
        <form action="{{ route('dashboard.invoice.genrate.mail') }}" method="post">
        @csrf 
        <div class="card-header">
          <div class="panel-heading">
            <div style="display: none" class="col-md-2" > 
                <select name="merchant_id" class="form-control">
                    <option value="{{request()->merchantname}}">{{request()->merchantname}}</option>
                </select>
            </div>
            <div class="col-md-12">
                <div class="row filter-row"> 
                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus"> 
                            <input type="text" class="form-control" name="reference_id" placeholder="Reference ID" value="{{ request()->reference_id }}" required="1">
                        </div> 
                    </div> 
                    <div class="col-md-2">
                       <button type="submit" class="btn btn-info" style="margin-right: 10px">@lang('Invoice Generate')</button>
                    </div>
                </div> 
            </div>
          </div>
        </div>

        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Invoice Generate</p>
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif
            @if(request()->merchantname)
                <div class="table-responsive">
                    <table id="example" class="display table table-bordered" style="width:100%">
                        <thead> 
                        <tr>
                            <th><input type="checkbox" class="select-all"/></th>
                            <th scope="col">Courier Id</th>
                            <th scope="col">Courier Tracking Id</th>
                            <th scope="col">Courier Type</th>
                            <th scope="col">Merchant name</th>
                            <th scope="col">Receiver Info</th> 
                            <th scope="col">Delivery Mode</th>
                            <th scope="col">COD Amount</th>
                            <th scope="col">Delivery Charge</th>
                            <th scope="col">Collectable</th>
                            <th scope="col">Mercahnt Payable</th>
                            <th scope="col">Status</th> 
                            <th scope="col">COD Payment</th>
                            <th scope="col">Paid By</th>
                            <th scope="col">Preferred method</th>
                            <th scope="col">Number</th>
                            <th scope="col">Time Duration</th> 
                            <th scope="col">Request Time</th> 
                            <th scope="col">Pickup Time</th> 
                            <th scope="col">Delivery Time</th> 
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($courier_requests as $courier_request)
                                <tr>
                                    <td> <input type="checkbox" name="ids[]" value="{{$courier_request->id}}"></td>
                                    <td>{{$courier_request->id}}</td>
                                    <td>
                                        @if(empty($courier_request->tracking_id))
                                            {{ 'Not Avaiable' }}
                                        @else
                                            {{$courier_request->tracking_id}}
                                        @endif
                                    </td>
                                    <td>{{$courier_request->courier_type->title}}</td>
                                    <td>{{(!empty($courier_request->customer->name) ? $courier_request->customer->name : 'Not Available')}}</td>
                                    <td>
                                        {{$courier_request->receiver_name}}<br>
                                        {{$courier_request->receiver_address}}<br>
                                        {{$courier_request->receiver_phone}}
                                    </td>
                                    <td>{{$courier_request->delivery_mode->title}}</td>
                                    <td>{{!empty($courier_request->cash_on_delivery_amount)?$courier_request->cash_on_delivery_amount:0}}</td>
                                    <td>{{$courier_request->pricing->price}}</td>
                                    <td>
                                        @if(!empty($courier_request->cash_on_delivery_amount))
                                            @if($courier_request->paid_by == "sender")
                                                {{ $courier_request->cash_on_delivery_amount }}
                                            @elseif($courier_request->paid_by == "receiver")
                                                {{ $courier_request->cash_on_delivery_amount +  $courier_request->pricing->price}}
                                            @elseif($courier_request->paid_by == "merged_with_cod")
                                                {{ $courier_request->cash_on_delivery_amount }}
                                            @endif
                                        @else
                                            @if($courier_request->paid_by == "merged_with_cod" || $courier_request->paid_by == "sender")
                                                0
                                            @else
                                                {{$courier_request->pricing->price}}
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($courier_request->cash_on_delivery_amount))
                                            @if($courier_request->paid_by == "sender")
                                                {{ $courier_request->cash_on_delivery_amount }}
                                            @elseif($courier_request->paid_by == "receiver")
                                                {{ $courier_request->cash_on_delivery_amount}}
                                            @elseif($courier_request->paid_by == "merged_with_cod")
                                                {{ $courier_request->cash_on_delivery_amount -  $courier_request->pricing->price}}
                                            @endif
                                        @else
                                            @if($courier_request->paid_by == "merged_with_cod")
                                                {{ 0 -  $courier_request->pricing->price}}
                                            @else
                                                0
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        {{!empty($courier_request->status->name)?$courier_request->status->name:"Not Available"}}
                                    </td>
                                    
                                    <td>
                                        @if(!empty($courier_request->cash_on_delivery_amount))
                                            @if($courier_request->cod_payment_status == "no" || empty($courier_request->cod_payment_status))
                                                <span style="color: red;"><strong>Payment Not Done Yet</strong></span>
                                            @elseif($courier_request->cod_payment_status == "yes")
                                                <span style="color: green;"><strong>Payment Done</strong></span>
                                            @endif
                                        @else
                                            <span style="color: red;"><strong>Not Available</strong></span>
                                        @endif        
                                    </td>
                                    <td>{{ $courier_request->paid_by }}</td>

                                    <td>
                                        @if($courier_request->cod_payment_status == "yes")
                                            {{ (!empty($courier_request->preferred_method) ? $courier_request->preferred_method : 'Not Available') }}
                                        @else
                                            {{ (!empty($courier_request->customer->preferred_method) ? $courier_request->customer->preferred_method : 'Not Available') }}
                                        @endif
                                    </</td>
                                    <td>
                                        @if($courier_request->cod_payment_status == "yes")
                                            {{ (!empty($courier_request->preferred_method_number) ? $courier_request->preferred_method_number : 'Not Available') }}
                                        @else
                                            @if($courier_request->customer->preferred_method == "bkash")
                                                {{ $courier_request->customer->bkash_no }}
                                            @elseif($courier_request->customer->preferred_method == "nagad")
                                                {{ $courier_request->customer->nagad_no }}
                                            @elseif($courier_request->customer->preferred_method == "rocket")
                                                {{ $courier_request->customer->rocket_no }}
                                            @elseif($courier_request->customer->preferred_method == "bank")
                                                {{ $courier_request->customer->bank_ac_no }}<br>
                                                {{ $courier_request->customer->bank_name }}
                                            @else
                                                {{ "Not Available" }}
                                            @endif
                                        @endif
                                    </td>

                                    <td>{{ $courier_request->created_at->diffForHumans() }}</td>
                                    <td>{{ date('d-m-Y h:i A',strtotime($courier_request->created_at)) }}</td>
                                    @php
                                    $picks =\App\Models\CourierRequestLog::where('courier_id',$courier_request->id)->where('status_id','13')->first();
                                    @endphp
                                    <td>{{ !empty($picks)? date('d-m-Y h:i A',strtotime($picks->created_at)) :"Not Available" }}</td>
                                     <td>{{ !empty($courier_request->delivery_date)?date('d-m-Y h:i A',strtotime($courier_request->delivery_date)):"Not Available" }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            {{ $courier_requests->links() }}
            @endif

        </div>
                
    </form>


@endsection
@push('custom-css')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
<link href="https://administration.holisterbd.com/public/assets/css/bootstrap-toggle.min.css" rel="stylesheet">




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
    $(document).ready(function() {
    $('#example').DataTable( {
        pageLength: 200,
        
        lengthMenu: [ [10, 25, 50,100,200], [10, 25, 50,100,200] ],
        dom: 'lBf',
        buttons: [
            'excel'
        ]
    } );
} );
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
