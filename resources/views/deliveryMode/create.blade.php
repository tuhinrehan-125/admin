@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Delivery Mode Create Form</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('dashboard.delivery.mode.store.create') }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="example-title" class="form-control-label">Delivery Mode Title</label>
                    <input class="form-control" type="text" name="title" placeholder="title" id="example-title">
                </div>
                <div class="form-group">
                    <label for="example-time" class="form-control-label">Time(Hours)</label>
                    <input class="form-control" type="number" name="time_in_hours" placeholder="time in hours" id="example-time">
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Courier Type</label>
                    </div>
                    <select name="courier_type_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($courier_types as $courier_type)
                        <option value="{{$courier_type->id}}">{{$courier_type->title}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Buy4U Type</label>
                    </div>
                    <select name="buy4u_type_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($buy4u_types as $buy4u_type)
                            <option value="{{$buy4u_type->id}}">{{$buy4u_type->title}}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Save Delivery Mode</button>
                </div>
            </form>
        </div>
    </div>
@endsection
