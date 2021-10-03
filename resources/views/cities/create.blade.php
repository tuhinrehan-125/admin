@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Add City Form</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('dashboard.city.store.create') }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="example-title" class="form-control-label">City Name</label>
                    <input class="form-control" type="text" name="name" placeholder="city name" id="example-title">
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Save City</button>
                </div>
            </form>
        </div>
    </div>
@endsection
