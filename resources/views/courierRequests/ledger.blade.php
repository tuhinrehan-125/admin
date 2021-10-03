@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
         <form> 
            <div class="col-md-12">
                <div class="row filter-row"> 
                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus">
                            <label class="focus-label">Invoice Number</label>
                            <input type="text" class="form-control " name="number" value="{{ request()->number }}">
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus"> 
                            <label class="focus-label">Reference Id</label>
                            <input type="text" class="form-control " name="reference_id" value="{{ request()->reference_id }}">
                        </div> 
                    </div> 
                   
                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus">
                            <label class="focus-label">Generate Date</label>
                            <input type="date" value="{{ request()->pickupdate }}" class="form-control input-medium search-query" name="pickupdate" onkeypress="return true">
                        </div>
                    </div>
                    
                    <div class="col-sm-6 col-md-2">
                        <label class="focus-label"></label>
                        <button class="btn btn-success btn-block"> Search </button>
                    </div>
                </div>
            </div>
        </form>

        @php
            $cods = 0;
            $delivery_charges = 0;
            $mercahnt_payables = 0;
        
        foreach($courier_requests as $courier_request){
            $list = \App\Models\InvoiceList::where('invoice_id',$courier_request->id)->get();
            $cods = $cods+$list->sum('cod');
            $delivery_charges = $delivery_charges+$list->sum('delivery_charge');
            $mercahnt_payables = $mercahnt_payables+$list->sum('mercahnt_payable');
        }
        @endphp
        <div class="mt-5 ml-4" style="width: 100%;">
            <h3>Total COD: {{ $cods }}</h3>
            <h3>Total Delivery Charge: {{ $delivery_charges }}</h3>
            <h3>Total Merchant Paid: {{ $mercahnt_payables }}</h3>
        </div>
        

        <div class="card-body">
            <!-- <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Courier Requests Table</p> -->
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif

            <div class="table-responsive">
                <table id="example" class="display table table-bordered" style="width:100%">
                    <thead> 
                    <tr>
                        <th scope="col">Invoice Number</th>
                        <th scope="col">Reference Id</th>
                        <th scope="col">Total COD</th>
                        <th scope="col">Total Delivery Charge</th>
                        <th scope="col">Total Paid</th> 
                        <th scope="col">Created Date</th> 
                        <th scope="col">Generate By</th> 
                    </tr>
                    </thead>
                    <tbody>
                        
                        @foreach($courier_requests as $courier_request)
                            <tr>
                                <td><a href="{{ route('dashboard.invoice.show.list',$courier_request->id) }}">{{$courier_request->number}}</a></td>
                                <td>
                                    @if(empty($courier_request->reference_id))
                                        {{ 'Not Avaiable' }}
                                    @else
                                        {{$courier_request->reference_id}}
                                    @endif
                                </td>
                                @php
                                    $lists = \App\Models\InvoiceList::where('invoice_id',$courier_request->id)->get();
                                @endphp
                                <td> {{ $lists->sum('cod') }} </td>
                                <td> {{ $lists->sum('delivery_charge') }} </td>
                                <td> {{ $lists->sum('mercahnt_payable') }} </td>
                                <td>{{ date('d-m-Y h:i A',strtotime($courier_request->created_at)) }}</td>
                                 @php
                                    $adminstration = \App\Models\User::where('id',$courier_request->added_by)->first();
                                @endphp
                                <td> {{ !empty($adminstration->name)?$adminstration->name:"Not Available" }} </td> 
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
        
        lengthMenu: [ [10, 25, 50,100,500], [10, 25, 50,100,500] ],
        dom: 'lrtip',
        buttons: [
            'excel'
        ]
    } );
} );
</script>



@endpush
