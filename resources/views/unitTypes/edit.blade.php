@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Unit Type Update Form</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('dashboard.unit.type.update',$unit_type->id) }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="example-title" class="form-control-label">Unit Type Name</label>
                    <input class="form-control" type="text" name="title" placeholder="unit type" id="example-title" value="{{$unit_type->title}}">
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Update Unit Type</button>
                </div>
            </form>
        </div>
    </div>
@endsection
