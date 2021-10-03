@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div style="padding: 1.2em;">
            <a href="{{ route('dashboard.courier.type.create') }}" type="button" class="btn btn-primary">Create Courier Type</a >
        </div>
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Courier Types Table</p>

            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif
            <table id="example" class="display" style="width:100%">
                <thead class="thead-light">
                <tr>
                    <th scope="col">Title</th>
                    <th scope="col">Slug</th>
                    <th scope="col">Created At</th>
                    <th scope="col" class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($courier_types as $courier_type)
                        <tr>
                            <td>{{$courier_type->title}}</td>
                            <td>{{$courier_type->slug}}</td>
                            <td>{{$courier_type->created_at}}</td>
                            <td>
                                <div class="d-flex" style="justify-content: space-evenly;">
                                    <a href="{{ route('dashboard.courier.type.edit',$courier_type->id) }}" style="cursor: pointer;"><i class="ni ni-settings" style="font-size: 1.3em;"></i></a>
                                    <!--<a href="{{ route('dashboard.courier.courier.type.delete',$courier_type->id) }}" style="cursor: pointer;"><i class="ni ni-fat-remove" style="font-size: 1.3em;"></i></a>-->
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
