@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
         <form>
            <div class="col-md-12">
                <div class="row filter-row">
                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus">
                            <label class="focus-label">Courier ID</label>
                            <input type="text" class="form-control " name="courierid" value="{{ request()->courierid }}">
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus"> 
                            <label class="focus-label">Tracking ID</label>
                            <input type="text" class="form-control " name="trackingid" value="{{ request()->trackingid }}">
                        </div> 
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus">
                            <label class="focus-label">Date</label>
                            <input type="date" id="sdate" value="{{ request()->sdate }}" class="form-control input-medium search-query" name="sdate" onkeypress="return true">
                        </div>
                    </div>
                    <!--<div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus">
                            <label class="focus-label">End Date</label>
                            <input type="date" id="edate" value="{{ request()->edate }}" class="form-control input-medium search-query" name="edate" onkeypress="return true">
                        </div>
                    </div>-->
                    
                    
                    <div class="col-sm-6 col-md-2">
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
                        <th scope="col">Amount</th>
                        <th scope="col">Phone</th> 
                        <th scope="col">Transaction Id</th>
                        <th scope="col">Payment Time</th> 
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($courier_requests as $courier_request)

                            <tr>
                                <td>{{$courier_request->courier_id}}</td>
                                <td>
                                    @if(empty($courier_request->trackingid))
                                        {{ 'Not Avaiable' }}
                                    @else
                                        {{$courier_request->trackingid}}
                                    @endif
                                </td>
                                <td>{{!empty($courier_request->amount)?$courier_request->amount:0}}</td> 
                                <td>{{!empty($courier_request->phone)?$courier_request->phone:"Not Available"}}</td>
                                <td>{{!empty($courier_request->transaction_id)?$courier_request->transaction_id:"Not Available"}}</td>
                                <td>{{ date('d-m-Y h:i A',strtotime($courier_request->created_at)) }}</td>
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
        
        lengthMenu: [ [10, 25, 50,100], [10, 25, 50,100] ],
        dom: 'lBf',
        buttons: [
            'excel'
        ]
    } );
} );
</script>




@endpush
