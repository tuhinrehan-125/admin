@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
         <form action="{{ route('dashboard.courier.bulk.status.request.change') }}" method="post">
        @csrf 
        <div class="card-header">
          <div class="panel-heading">
            <div class="row">
              <div class="col-md-4"> 
                <h3 class="card-title">
                <strong> Status Change  </strong>
                </h3>  
              </div>
                
                <div class="col-md-3">
                    <select name="status_ids" class="form-control"> 
                         @foreach($statuses as $status)
                            <option value="{{$status->id}}">{{$status->name}}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <textarea class="form-control" type="text" name="note" placeholder="Notes"></textarea>
                </div>
                
                <div class="col-md-2">
                   <button type="submit" class="btn btn-info" style="margin-right: 10px">@lang('Submit')</button>
                </div>

            </div> 
          </div>
        </div>
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
                        <th><input type="checkbox" class="select-all"/></th>
                        <th scope="col">Courier Id</th>
                        <th scope="col">Courier Tracking Id</th>
                        <th scope="col">Courier Type</th>
                        <th scope="col">Merchant name</th>
                        <th scope="col">Receiver Address</th> 
                        <th scope="col">Receiver Name</th>
                        <th scope="col">Receiver Phone</th>
                        <th scope="col">Delivery Mode</th>
                        <th scope="col">COD Amount</th>
                        <th scope="col">Price</th>
                        <th scope="col">Status</th> 
                        <!--<th scope="col">COD Payment</th>--> 
                        <th scope="col">Time</th> 
                        <th scope="col" class="text-center">Action</th>
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
                                <td>{{$courier_request->receiver_address}}</td>
                                <td>{{$courier_request->receiver_name}}</td>
                                <td>{{$courier_request->receiver_phone}}</td>
                                <td>{{$courier_request->delivery_mode->title}}</td>
                                <td>{{!empty($courier_request->cash_on_delivery_amount)?$courier_request->cash_on_delivery_amount:'Not Available'}}</td>
                                <td>{{$courier_request->pricing->price}}</td>
                                <td>
                                    {{!empty($courier_request->status->name)?$courier_request->status->name:"Not Available"}}
                                </td>
                                <!--<td>
                                    @if($courier_request->cash_on_delivery == '1')
                                        @if($courier_request->cod_payment_status == "no" || empty($courier_request->cod_payment_status))
                                            
                                            <a href="{{ route('dashboard.status.cod.courier.request.payment.status',[$courier_request->id,'no']) }}" type="button" class="btn btn-danger btn-sm active">No</a>
                                            <a href="{{ route('dashboard.status.cod.courier.request.payment.status',[$courier_request->id,'yes']) }}" type="button" class="btn btn-primary btn-sm">Yes</a>

                                        @elseif($courier_request->cod_payment_status == "yes")
                                            <span style="color: green;"><strong>Payment Done</strong></span>
                                        @endif
                                    @else
                                        <span style="color: red;"><strong>Not Available</strong></span>
                                    @endif
                                </td>-->
                                <td>{{ date('d-m-Y h:i A',strtotime($courier_request->created_at)) }}</td>
                                <td>
                                    <div class="d-flex" style="justify-content: space-evenly;">

                                        <a href="{{ route('courier-request-hub-transfer',$courier_request->id) }}" style="cursor: pointer; padding: 0 0.5em;"><i class="fa fa-forward" style="font-size: 1.3em;"></i></a>
                                        <a href="{{ route('courier-request-printer',$courier_request->id) }}" style="cursor: pointer; padding: 0 0.5em;"><i class="fa fa-print" style="font-size: 1.3em;"></i></a>
                                        <a href="{{ route('dashboard.courier.request.info',$courier_request->id) }}" style="cursor: pointer; padding: 0 0.5em;"><i class="ni ni-ungroup" style="font-size: 1.3em;"></i></a>
                                        <a href="{{ route('dashboard.courier.request.edit',$courier_request->id) }}" style="cursor: pointer; padding: 0 0.5em;"><i class="ni ni-settings" style="font-size: 1.3em;"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>
    </div>

@endsection
@push('custom-css')

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">

@endpush
@push('scripts')
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
    $('#example').DataTable( {
        pageLength: 10,
        "pagingType": "full_numbers",
        lengthMenu: [ [10, 25, 50,100, -1], [10, 25, 50,100, "All"] ],
        dom: 'lBfrtip',
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

        function changeStatus(id){
            document.getElementById('changeStatusForm_'+id).submit();
        }
        function changeStatuses(id){
            document.getElementById('changeStatusFormes_'+id).submit();
        }
        
    </script>
@endpush
