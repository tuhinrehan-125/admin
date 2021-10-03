@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">

            <div class="col-md-12">
                <div class="row filter-row">
                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus">
                            <label class="focus-label">ISD/OSD</label>
                            <select class="select select2-hidden-accessible form-control" data-select2-id="1" tabindex="-1" aria-hidden="true" name="hubs" id="hubs">
                                <option value="">Select Hub</option>
                                <option value="3">ISD</option>
                                <option value="4">OSD</option>
                                   
                            </select> 
                        </div>  
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus">
                            <label class="focus-label">Merchant Name</label>
                            <select class="select select2-hidden-accessible form-control" data-select2-id="1" tabindex="-1" aria-hidden="true" name="merchantname" id="merchantname">
                                <option value="">Select Merchant Name</option>
                                    @foreach($users=\App\Models\User::where('type','merchant')->orwhere('type','individual')->select('id','name')->orderBy('name')->get() as $user)
                                        <option @if(request()->merchantname == $user->id) selected @endif value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                            </select>
                        </div> 
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus">
                            <label class="focus-label">Start Date</label>
                            <input type="date" id="sdate" value="{{ request()->sdate }}" class="form-control input-medium search-query" name="sdate" onkeypress="return true">
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus">
                            <label class="focus-label">Start Time</label>
                            <input type="time" id="stime" value="{{ request()->stime }}" class="form-control input-medium search-query" name="stime" onkeypress="return true">
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus">
                            <label class="focus-label">End Date</label>
                            <input type="date" id="edate" value="{{ request()->edate }}" class="form-control input-medium search-query" name="edate" onkeypress="return true">
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-2">
                        <div class="form-group form-focus">
                            <label class="focus-label">End Time</label>
                            <input type="time" id="etime" value="{{ request()->etime }}" class="form-control input-medium search-query" name="etime" onkeypress="return true">
                        </div>
                    </div>
                    
                    <div class="col-sm-6 col-md-2">
                        <label class="focus-label"></label>
                        <button class="btn btn-success btn-block" id="btnSearch"> Search </button>
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
            <div class="panel-body table-responsive" id="exampletable">
                <table width="100%" class="table table-striped table-bordered table-hover" id="example">
                    <thead> 
                    <tr>
                        <th>Courier Id</th>
                        <th>Tracking Id</th>
                        <th>Merchant Info</th>
                        <th>COD Amount</th>
                        <th>Delivery Charge</th>
                        <th>Collectable</th>
                        <th>Mercahnt Payable</th>
                        <th>Paid By</th>
                        <th>PickUp Hub</th>
                        <th>Deliver Hub</th>
                        <th>Status</th> 
                        <th>Hub Payment</th> 
                        <th>COD Payment</th>
                        <th>Pickup Time</th> 
                        <th>Delivery Time</th> 
                        <th class="text-center">Action</th>
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
                                
                                <td>{{ (!empty($courier_request->customer->name) ? $courier_request->customer->name : 'Not Available') }}</td>
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
                                <td>{{ $courier_request->paid_by }}</td>
                                <td>{{ !empty($courier_request->branch->name)?$courier_request->branch->name:"Not Available" }} </td>
                                <td>
                                    @if($courier_request->delivery_hub == '0' || empty($courier_request->delivery_hub))
                                        {{ !empty($courier_request->branch->name)?$courier_request->branch->name:"Not Available" }}
                                    @else
                                        {{ !empty(\App\Models\Branch::find($courier_request->delivery_hub)->name)?\App\Models\Branch::find($courier_request->delivery_hub)->name:"Not Available" }}
                                    @endif 
                                </td>
                                <td>{{!empty($courier_request->status->name)?$courier_request->status->name:"Not Available"}}</td>
                                <td>{{!empty($courier_request->hub_payment)?$courier_request->hub_payment:"Not Available"}}</td>
                                <td>
                                    @if(!empty($courier_request->cash_on_delivery_amount))
                                        @if($courier_request->cod_payment_status == "no" || empty($courier_request->cod_payment_status))
                                            No</strong></span>
                                        @elseif($courier_request->cod_payment_status == "yes")
                                            Yes
                                        @endif
                                    @else
                                        Not Available
                                    @endif
                                </td>
                                <td> {{ date('d-m-Y h:i A',strtotime($courier_request->created_at)) }}</td>
                                <td>{{ !empty($courier_request->delivery_date)?date('d-m-Y h:i A',strtotime($courier_request->delivery_date)):"Not Available" }}</td>
                                <td>
                                    <div class="d-flex" style="justify-content: space-evenly;">

                                        <a href="{{ route('courier-request-printer',$courier_request->id) }}" style="cursor: pointer; padding: 0 0.5em;"><i class="fa fa-print" style="font-size: 1.3em;"></i></a>
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

 <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.0/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">





@endpush
@push('scripts')

    
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/1.0.7/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.0/js/buttons.print.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
        $(document).ready(function() {
           
           function dataTable(){

                $('#example').DataTable( {
                    pageLength: 10,
                    lengthMenu: [ [10, 25, 50,100, -1], [10, 25, 50, 100,"All"] ],
                    dom: 'lBfrtip',
                    buttons: [
                        'excelHtml5',
                    ], 
                });
           }
           dataTable();
            $('#btnSearch').click(function(){
                var sdate=$('#sdate').val();
                var stime=$('#stime').val();
                var edate=$('#edate').val();
                var etime=$('#etime').val();
                var merchantname=$('#merchantname').val();
                var hubs=$('#hubs').val();

                if(sdate != "" && stime != "" && edate != "" && etime != "" && merchantname != "" && hubs != ""){
                    var dataobj={type:4,sdate:sdate,stime:stime,edate:edate,etime:etime,merchantname:merchantname,hubs:hubs};
                }
                else if(sdate != "" && stime != "" && edate != "" && etime != "" && merchantname != "" ){
                    var dataobj={type:1,sdate:sdate,stime:stime,edate:edate,etime:etime,merchantname:merchantname};
                }
                else if(sdate != "" && stime != "" && edate != "" && etime != "" && hubs != "" ){
                    var dataobj={type:5,sdate:sdate,stime:stime,edate:edate,etime:etime,hubs:hubs};
                }
                else if(sdate != "" && stime != "" && edate != "" && etime != ""){
                    var dataobj={type:2,sdate:sdate,stime:stime,edate:edate,etime:etime};
                }
                else if (merchantname != ""){
                    var dataobj={type:3,merchantname:merchantname};
                }
                else{
                    null;
                }
                $.ajax({
                    url: "{{ route('dashboard.search.nagad.daily_nagad_transactions.accounts') }}",
                    method: 'GET',
                    data: dataobj,
                    dataType:'json',
                    success:function(data){
                        $('#exampletable').html(data);
                        dataTable();
                    }
                });
            });
            
        });
    </script>


@endpush


