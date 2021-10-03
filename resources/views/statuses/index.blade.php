@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div style="padding: 1.2em;">
            <a href="{{ route('dashboard.status.create') }}" type="button" class="btn btn-primary">Status Create</a >
        </div>
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Statuses Table</p>
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif
            <table id="example" class="display" style="width:100%">
                <thead class="thead-light">
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Sequence</th>
                    <th scope="col">Created At</th>
                    <!--<th scope="col" class="text-center">Action</th>-->
                </tr>
                </thead>
                <tbody>
                @foreach($statuses as $status)
                    <tr>
                        <td>{{$status->name}}</td>
                        <td>{{$status->sequence}}</td>
                        <td>{{$status->created_at}}</td>
                        <!--<td>
                            <div class="d-flex" style="justify-content: space-evenly;">
                                <a href="{{ route('dashboard.status.edit',$status->id) }}" style="cursor: pointer;"><i class="ni ni-settings" style="font-size: 1.3em;"></i></a>
                                <a href="{{ route('dashboard.status.delete',$status->id) }}" style="cursor: pointer;"><i class="ni ni-fat-remove" style="font-size: 1.3em;"></i></a>
                            </div>
                        </td>-->
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
