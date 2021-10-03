@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div style="padding: 1.2em;">
            <a href="{{ route('dashboard.buy4u.create') }}" type="button" class="btn btn-primary">Create Buy4u Request</a >
        </div>
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Buy4u Requests Table</p>
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif
            <table id="example" class="display" style="width:100%">
                <thead class="thead-light">
                <tr>
                    <th scope="col">Buy4u Type</th>
                    <th scope="col">City</th>
                    <th scope="col">Area</th>
                    <th scope="col">Address</th>
                    <th scope="col">Preferred Shop Name</th>
                    <th scope="col">Preferred Shop Address</th>
                    <th scope="col">Price</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($buy4u_requests as $request)
                        <tr>
                            <td>{{$request->buy4u_type->title}}</td>
                            <td>{{$request->city->name}}</td>
                            <td>{{$request->area->name}}</td>
                            <td>{{$request->address}}</td>
                            <td>{{$request->preferred_shop_name}}</td>
                            <td>{{$request->preferred_shop_address}}</td>
                            <td>{{!empty($request->pricing->price)?$request->pricing->price:'Not Available'}}</td>
                            <td>
                                <form id="changeStatusForm" action="{{ route('dashboard.buy4u.update.status',$request->id) }}" method="post">
                                    @csrf
                                    <select name="status_id" onchange="changeStatus()">
                                        @foreach($statuses as $status)
                                            <option value="{{$status->id}}" {{$status->id==$request->status_id?'selected':''}}>{{$status->name}}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td>
                                <div class="d-flex" style="justify-content: space-evenly;">
                                    <a href="{{ route('dashboard.buy4u.show',$request->id) }}" style="cursor: pointer; padding: 0 0.5em;"><i class="ni ni-ungroup" style="font-size: 1.3em;"></i></a>
                                    <a href="{{ route('dashboard.buy4u.edit',$request->id) }}" style="cursor: pointer; padding: 0 0.5em;"><i class="ni ni-settings" style="font-size: 1.3em;"></i></a>
                                    <!--<a href="{{ route('dashboard.buy4u.request.delete',$request->id) }}" style="cursor: pointer; padding: 0 0.5em;"><i class="ni ni-fat-remove" style="font-size: 1.3em;"></i></a>-->
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $('#example').DataTable();
        } );
        function changeStatus(){
            document.getElementById('changeStatusForm').submit();
        }
    </script>
@endpush
