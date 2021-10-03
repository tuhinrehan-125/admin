@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Hub Account</p>
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}} 
                </div>
            @endif
            <form> 
                <div class="col-md-12">
                    <div class="row filter-row"> 
                        <div class="col-sm-6 col-md-2">
                            <div class="form-group form-focus">
                                <select class="select select2-hidden-accessible form-control" data-select2-id="1" tabindex="-1" aria-hidden="true" name="hubs" id="hubs">
                                    <option value="">Select Hub</option>
                                    <option @if(request()->hubs == '3') selected @endif value="3">ISD</option>
                                    <option @if(request()->hubs == '4') selected @endif value="4">OSD</option>
                                       
                                </select> 
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-2">
                            <button class="btn btn-success btn-block"> Search </button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <table id="example" class="display table table-bordered" style="width:100%">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">Hub Name</th>
                        <th scope="col">Agent</th>
                        <th scope="col">Total Parcel Delivered</th>
                        <th scope="col">Today Parcel Delivered</th>
                        <th scope="col">Total Deliver Charge</th>
                        <th scope="col">Total COD</th>
                        <th scope="col">Total Due</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($branches as $branch) 
                        <tr>
                            <td>{{$branch->name}}</td>
                            <td>{{$branch->is_agent?'Yes':'No'}}</td>
                            @php
                                $hubs  = \App\Models\CourierRequest::where('delivery_hub',$branch->id);
                                if (date('Y-m-d H:i:s') < date("Y-m-d 14:00:00")) {
                                    $to = date('Y-m-d 14:00:00');
                                    $from = date("Y-m-d 14:00:00", strtotime("-1 day"));
                                } else {
                                    $from = date('Y-m-d 14:00:00');
                                    $to = date("Y-m-d 14:00:00", strtotime("+1 day"));
                                }

                            @endphp
                            <td>
                                @php
                                    $deliver_count = \App\Models\CourierRequest::where('status_id','18')->where('delivery_hub',$branch->id)->count();
                                    $pickup_count = \App\Models\CourierRequest::where('status_id','18')->where('delivery_hub','0')->where('branch_id',$branch->id)->count();
                                @endphp
                                {{ $deliver_count + $pickup_count }}
                            </td>
                            <td>
                                @php
                                    $delivery_hub = count($hubs->join('courier_request_logs','courier_request_logs.courier_id','courier_requests.id')->where('courier_request_logs.status_id','18')->whereBetween('courier_request_logs.created_at',[$from, $to])->get());

                                    $pickup_hub = count(\App\Models\CourierRequest::join('courier_request_logs','courier_request_logs.courier_id','courier_requests.id')->where('courier_request_logs.status_id','18')->where('delivery_hub','0')->where('branch_id',$branch->id)->whereBetween('courier_request_logs.created_at',[$from, $to])->get());

                                @endphp
                                {{ $delivery_hub + $pickup_hub }}
                            </td>
  
                                @php
                                    $delivery_hub_cod = \App\Models\CourierRequest::where('delivery_hub',$branch->id)->where('status_id','18')->where('hub_payment','no')->sum('cash_on_delivery_amount');

                                    $delivery_hub_merge_cod = \App\Models\CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('delivery_hub',$branch->id)->where('hub_payment','no')->where('courier_requests.paid_by','merged_with_cod')->sum('pricings.price');

                                    $delivery_hub_cod_amount = $delivery_hub_cod - $delivery_hub_merge_cod;

                                    $pickup_hub_cod = \App\Models\CourierRequest::where('status_id','18')->where('delivery_hub','0')->where('branch_id',$branch->id)->where('hub_payment','no')->sum('cash_on_delivery_amount');

                                    $pickup_hub_cod_merge_cod = \App\Models\CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('delivery_hub','0')->where('branch_id',$branch->id)->where('hub_payment','no')->where('courier_requests.paid_by','merged_with_cod')->sum('pricings.price');

                                    $pickup_hub_cod_amount = $pickup_hub_cod - $pickup_hub_cod_merge_cod;

                                    $delivery_hub_charge = \App\Models\CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('delivery_hub',$branch->id)->where('paid_by','!=','sender')->where('hub_payment','no')->sum('pricings.price');

                                    $pickup_hub_charge = \App\Models\CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('delivery_hub','0')->where('branch_id',$branch->id)->where('hub_payment','no')->sum('pricings.price');

                                    $pickup_hub_delivery_charge = \App\Models\CourierRequest::join('pricings','pricings.id','courier_requests.pricing_id')->where('courier_requests.status_id','18')->where('delivery_hub','!=','0')->where('branch_id',$branch->id)->where('hub_payment','no')->where('paid_by','sender')->sum('pricings.price');
                                
                                @endphp
                                <td>{{ $delivery_hub_charge + $pickup_hub_charge + $pickup_hub_delivery_charge }}</td>
                                <td>{{ $delivery_hub_cod_amount +  $pickup_hub_cod_amount }}</td>
                                <td>
                                <a href="{{ route('dashboard.account.hub.list.show',$branch->id) }}">
                                {{ $delivery_hub_cod_amount +  $pickup_hub_cod_amount + $delivery_hub_charge + $pickup_hub_charge + $pickup_hub_delivery_charge }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>  
        </div>
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
