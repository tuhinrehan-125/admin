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
                            <label class="focus-label">Merchant Name</label>
                            <select class="select select2-hidden-accessible form-control" data-select2-id="1" tabindex="-1" aria-hidden="true" name="merchantname">
                                <option value="">Select Merchant Name</option>
                                    @foreach($users=\App\Models\User::where('type','merchant')->orwhere('type','individual')->select('id','name')->orderBy('name')->get() as $user)
                                        <option @if(request()->merchantname == $user->id) selected @endif value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-sm-6 col-md-3">
                        <label class="focus-label"></label>
                        <button class="btn btn-success btn-block"> Search </button>
                    </div>
                </div>
            </div>
        </form>
       
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Courier Requests Table</p>
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif

            <div class="table-responsive">
                <table id="example" class="display table table-bordered" style="width:100%">
                    <thead> 
                    <tr>
                        <th scope="col">Courier Id</th>
                        <th scope="col">Courier Tracking Id</th>
                        <th scope="col">Courier Type</th>
                        <th scope="col">Merchant name</th>
                        <th scope="col">Receiver Info</th> 
                        <th scope="col">Delivery Mode</th>
                        <th scope="col">COD Amount</th>
                        <th scope="col">Delivery Charge</th>
                        <th scope="col">Paid By</th>
                        <th scope="col">PickUp Hub</th>
                        <th scope="col">Deliver Hub</th>
                        <th scope="col">Status</th> 
                        <th scope="col">COD Payment</th>
                        <th scope="col">Time Duration</th> 
                        <th scope="col">Request Time</th> 
                        <th scope="col">Pickup Time</th> 
                        <th scope="col">Delivery Time</th> 
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($courier_requests as $courier_request)
                            
                            <tr>
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
                                <td>{{ $courier_request->paid_by }}</td>
                                <td>{{ !empty($courier_request->branch->name)?$courier_request->branch->name:"Not Available" }} </td>
                                <td>
                                    @if($courier_request->delivery_hub == '0' || empty($courier_request->delivery_hub))
                                        {{ !empty($courier_request->branch->name)?$courier_request->branch->name:"Not Available" }}
                                    @else
                                        {{ !empty(\App\Models\Branch::find($courier_request->delivery_hub)->name)?\App\Models\Branch::find($courier_request->delivery_hub)->name:"Not Available" }}
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
                               

                               
                                <td>{{ $courier_request->created_at->diffForHumans() }}</td>
                                <td>{{ date('d-m-Y h:i A',strtotime($courier_request->created_at)) }}</td>
                                @php
                                $picks =\App\Models\CourierRequestLog::where('courier_id',$courier_request->id)->where('status_id','13')->first();
                                @endphp
                                <td>{{ !empty($picks)? date('d-m-Y h:i A',strtotime($picks->created_at)) :"Not Available" }}</td>
                                 <td>{{ !empty($courier_request->delivery_date)?date('d-m-Y h:i A',strtotime($courier_request->delivery_date)):"Not Available" }}</td>
                                <td>
                                    <div class="d-flex" style="justify-content: space-evenly;">                                        
                                        <a href="{{ route('dashboard.courier.request.info',$courier_request->id) }}" style="cursor: pointer; padding: 0 0.5em;"><i class="ni ni-ungroup" style="font-size: 1.3em;"></i></a>
                                    </div>
                                </td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{ $courier_requests->links() }}



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
        pageLength: 10,
        
        lengthMenu: [ [10, 25, 50,100], [10, 25, 50,100] ],
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
<script>
  $(function() {
    $('.toggle-class').change(function() {
        var status = $(this).prop('checked') == true ? 'yes' : 'no'; 
        var courier_id = $(this).data('id'); 
         
        $.ajax({
            type: "GET",
            dataType: "json",
            url: 'courier/cod_payment_request/',
            data: {'status': status, 'courier_id': courier_id},
            success: function(data){
              console.log(data.success)
            }
        });
    })
  })
</script>

@endpush
