@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div style="padding: 1.2em;">
            <a href="{{ route('dashboard.branch.create') }}" type="button" class="btn btn-primary">Add Hub</a >
        </div>
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Hub Table</p>
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif
            <table id="example" class="display" style="width:100%">
                <thead class="thead-light">
                <tr>
                    <th scope="col">Hub Name</th>
                    <th scope="col">Hub Email</th>
                    <th scope="col">Hub Phone</th> 
                    <th scope="col">Address</th>
                    <!--<th scope="col">Area Name</th>-->
                    <th scope="col">City Name</th>
                    <th scope="col">Agent</th>
                    <th scope="col" class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($branches as $branch)
                    <tr>
                        <td>{{$branch->name}}</td>
                        <td>{{$branch->email}}</td>
                        <td>{{$branch->phone}}</td>
                        <td>{{$branch->address}}</td>
                        <!--<td>-->
                            <?php
                                /*$hub_ids = \App\Models\HubArea::where('hub_id',$branch->id)->get();*/
                                /*foreach($hub_ids as $hub_id){
                                    echo $hub_id->area->name;*/
                                    ?>
                            <?php /*}*/  ?>
                        <!--</td>-->
                        <td>{{!empty($branch->city->name)?$branch->city->name:"All Area"}}</td>
                        <td>{{$branch->is_agent?'yes':'no'}}</td>
                        <td>
                            <div class="d-flex" style="justify-content: space-evenly;">
                                <a href="{{ route('dashboard.branch.edit',$branch->id) }}" style="cursor: pointer;"><i class="ni ni-settings" style="font-size: 1.3em;"></i></a>
                                <!--<a href="{{ route('dashboard.branch.delete',$branch->id) }}" style="cursor: pointer;"><i class="ni ni-fat-remove" style="font-size: 1.3em;"></i></a>-->
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
