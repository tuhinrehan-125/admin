@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        @if(Auth::user()->type == "admin")
        <div style="padding: 1.2em;">
            <a href="{{ route('dashboard.pricing.create') }}" type="button" class="btn btn-primary">Add Pricing</a >
        </div>
        @endif
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Pricing Table</p>
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif
            <table id="example" class="display" style="width:100%">
                <thead class="thead-light"> 
                <tr>
                    <th scope="col">Courier Type</th>
                    <th scope="col">Delivery Mode</th>
                    <th scope="col">Min Weight</th>
                    <th scope="col">Max Weight</th>
                    <th scope="col">Price</th>
                    <th scope="col">Special</th>
                    @if(Auth::user()->type == "admin")
                    <th scope="col" class="text-center">Action</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                @foreach($pricings as $pricing)
                    <tr>
                        <td>{{ !empty($pricing->courier_type->title)?$pricing->courier_type->title:"Not Available" }}</td>
                        <td>{{ !empty($pricing->delivery_mode->title)?$pricing->delivery_mode->title:"Not Available" }} </td>
                        <td>{{$pricing->min_weight}}</td>
                        <td>{{$pricing->max_weight}}</td>
                        <td>{{$pricing->price}}</td>
                        <td>{{ !empty($pricing->user->name)?$pricing->user->name:"Regular" }}</td>
                        @if(Auth::user()->type == "admin")
                        <td>
                            <div class="d-flex" style="justify-content: space-evenly;">
                                <a href="{{ route('dashboard.pricing.edit',$pricing->id) }}" style="cursor: pointer;"><i class="ni ni-settings" style="font-size: 1.3em;"></i></a>
                                <!-- <a href="{{ route('dashboard.pricing.delete',$pricing->id) }}" style="cursor: pointer;"><i class="ni ni-fat-remove" style="font-size: 1.3em;"></i></a> -->
                            </div>
                        </td>
                        @endif
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
    </script>
@endpush
