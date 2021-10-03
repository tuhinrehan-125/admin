@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div style="padding: 1.2em;">
            <a href="{{ route('dashboard.area.create') }}" type="button" class="btn btn-primary">Add Area</a >
        </div>
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Areas Table</p>
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif
            <table id="example" class="display" style="width:100%">
                <thead class="thead-light">
                <tr>
                    <th scope="col">Area Id</th>
                    <th scope="col">Area Name</th>
                    <th scope="col">City Name</th>
                    <th scope="col">Hub Name</th>
                    <th scope="col">Created At</th>
                    <th scope="col" class="text-center">Action</th>
                </tr> 
                </thead>
                <tbody>
                @foreach($areas as $area)
                    <tr>
                        <td>{{$area->id}}</td>
                        <td>{{$area->name}}</td>
                        <td>{{!empty($area->city->name) ? $area->city->name : "Not Availble"}}</td>
                        <td>
                            @php
                               $hubs = \App\Models\HubArea::where('area_id',$area->id)->first();
                               if(!empty($hubs)){
                                    $hub_name = \App\Models\Branch::find($hubs->hub_id)->name;
                                } else {
                                    $hub_name = "Not Available";
                                }
                            @endphp
                            {{ $hub_name }}

                        </td>
                        <td>{{$area->created_at}}</td>
                        <td>
                            <div class="d-flex" style="justify-content: space-evenly;">
                                <a href="{{ route('dashboard.area.edit',$area->id) }}" style="cursor: pointer;"><i class="ni ni-settings" style="font-size: 1.3em;"></i></a>
                               <!--  <a href="{{ route('dashboard.area.delete',$area->id) }}" style="cursor: pointer;"><i class="ni ni-fat-remove" style="font-size: 1.3em;"></i></a> -->
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
    </script>
@endpush
