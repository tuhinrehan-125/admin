@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Update Area</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('dashboard.area.update',$area->id) }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="example-title" class="form-control-label">Area Name</label>
                    <input class="form-control" type="text" name="name" placeholder="Area name" id="example-title" value="{{$area->name}}">
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select City</label>
                    </div>
                    <select name="city_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($cities as $city)
                            <option value="{{$city->id}}" {{$city->id==$area->city_id?'selected':''}}>{{$city->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Update Area</button>
                </div>
            </form>
        </div>
    </div>
@endsection
