@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Supervisor Table</p>
            <div class="table-responsive">
                <table id="example" class="display table table-bordered" style="width:100%">
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th> 
                        <th scope="col">Type</th>
                        <th scope="col">Pickup</th>
                        <th scope="col">Delivery</th>
                        <th scope="col">Due</th>
                        <th scope="col">Paid</th>
                        <th scope="col">Hub</th> 
                        <th scope="col" class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{$user->name}}</td>
                            <td>{{$user->email}}</td>
                            <td>{{$user->phone}}</td>
                            <td>{{ucfirst($user->type)}} </td>
                            
                            @php
                                $pickup_agent_com_due = \App\Models\CourierRequest::where('branch_id',$user->hub_id)->where('pickup_agent_id',$user->id)->where('pickup_agent_commission_payment','no')->sum('pickup_agent_commission');
                                $delivery_agent_com_due = \App\Models\CourierRequest::where('delivery_hub',$user->hub_id)->where('delivery_agent_id',$user->id)->where('delivery_agent_commission_payment','no')->sum('delivery_agent_commission');
                            @endphp
                            <td><a href="{{ route('dashboard.pickup.agent.list',$user->id) }}">{{ $pickup_agent_com_due }}</a></td>
                            <td><a href="{{ route('dashboard.delivery.agent.list',$user->id) }}">{{ $delivery_agent_com_due }}</a></td>
                            
                            @php
                                $pickup_agent_com_due = \App\Models\CourierRequest::where('branch_id',$user->hub_id)->where('pickup_agent_id',$user->id)->where('pickup_agent_commission_payment','no')->sum('pickup_agent_commission');
                                $delivery_agent_com_due = \App\Models\CourierRequest::where('delivery_hub',$user->hub_id)->where('delivery_agent_id',$user->id)->where('delivery_agent_commission_payment','no')->sum('delivery_agent_commission');
                            @endphp
                            <td>{{ $pickup_agent_com_due + $delivery_agent_com_due }}</td>
                            @php
                                $pickup_agent_com_paid = \App\Models\CourierRequest::where('branch_id',$user->hub_id)->where('pickup_agent_id',$user->id)->where('pickup_agent_commission_payment','yes')->sum('pickup_agent_commission');
                                $delivery_agent_com_paid = \App\Models\CourierRequest::where('delivery_hub',$user->hub_id)->where('delivery_agent_id',$user->id)->where('delivery_agent_commission_payment','yes')->sum('delivery_agent_commission');
                            @endphp
                            <td>{{ $pickup_agent_com_paid + $delivery_agent_com_paid }}</td>

                            <td>{{ !empty($user->hub_id)?\App\Models\Branch::find($user->hub_id)->name?\App\Models\Branch::find($user->hub_id)->name:'Not Existing':'Not Available' }}</td>
                            <td>
                                <div class="d-flex" style="justify-content: space-evenly;">
                                    <a href="{{ route('dashboard.user.edit',$user->id) }}" style="cursor: pointer;"><i class="ni ni-settings" style="font-size: 1.3em;"></i></a>
                                    <a onclick="return confirm('Are you sure to delete')" href="{{ route('dashboard.user.delete',$user->id) }}" style="cursor: pointer;"><i class="ni ni-fat-remove" style="font-size: 1.3em;"></i></a>
                                </div>
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
@endpush
