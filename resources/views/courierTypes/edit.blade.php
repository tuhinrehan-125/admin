@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Courier Type Update Form</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('dashboard.courier.type.update',$courier_type->id) }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="example-title" class="form-control-label">Courier Type</label>
                    <input class="form-control" type="text" name="title" placeholder="courier type" id="example-title" value="{{$courier_type->title}}">
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Update Courier Type</button>
                </div>
            </form>
        </div>
    </div>
@endsection
