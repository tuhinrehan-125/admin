@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div style="padding: 1.2em;">
            <a href="{{ route('dashboard.unit.type.create') }}" type="button" class="btn btn-primary">Unit Type Create</a >
        </div>
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Unit Types Table</p>
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif
            <table id="example" class="display" style="width:100%">
                <thead class="thead-light">
                <tr>
                    <th scope="col">Title</th>
                    <th scope="col">Created At</th>
                    <th scope="col" class="text-center">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($unit_types as $unit_type)
                    <tr>
                        <td>{{$unit_type->title}}</td>
                        <td>{{$unit_type->created_at}}</td>
                        <td>
                            <div class="d-flex" style="justify-content: space-evenly;">
                                <a href="{{ route('dashboard.unit.type.edit',$unit_type->id) }}" style="cursor: pointer;"><i class="ni ni-settings" style="font-size: 1.3em;"></i></a>
                               <!-- <a href="{{ route('dashboard.unit.type.delete',$unit_type->id) }}" style="cursor: pointer;"><i class="ni ni-fat-remove" style="font-size: 1.3em;"></i></a>-->
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
