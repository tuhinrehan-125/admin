@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Support List</p>
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif
            <table id="example" class="display" style="width:100%">
                <thead class="thead-light">
                <tr>
                    <th scope="col">Courier Id</th>
                    <th scope="col">Title</th>
                    <th scope="col">Status</th> 
                    <th scope="col">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($issues as $issue) 

                    <tr>
                        <td>{{!empty($issue->courier_id)?$issue->courier_id:"Not Available"}}</td>
                        <td>{{$issue->title}}</td>
                        <td>
                            @if($issue->status == 0)
                                <span style="color: red">Pending</span>
                            @elseif($issue->status == 1)
                                <span style="color: green">Answered</span>
                            @elseif($issue->status == 2)
                                <span style="color: blue">Solved</span>
                            @elseif($issue->status == 3)
                                <span style="color: green">Closed</span>
                            @endif
                        </td>
                        
                        <td> 
                            <a type="button" class="btn btn-primary" href="{{ route('dashboard.support.list.show',$issue->id) }}"><i class="fa fa-eye" aria-hidden="true"></i></a>
                            @if($issue->admin_view == '0') 
                                <img style="width: 60px;height: 30px" src="{{ asset('bell.svg') }}">
                            @endif

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
