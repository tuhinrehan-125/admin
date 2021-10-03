@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Add Pricing</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('dashboard.pricing.store.create') }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="example-name" class="form-control-label">Min Weight</label>

                    <input class="form-control" step="any" type="number" name="min_weight" placeholder="min weight">
                </div>
                <div class="form-group">
                    <label for="example-email" class="form-control-label">Max Weight</label>
                    <input class="form-control" step="any" type="number" name="max_weight" placeholder="max weight" >
                </div>
                <div class="form-group">
                    <label for="example-phone" class="form-control-label">Price</label>
                    <input class="form-control" type="number" name="price" placeholder="price" id="example-phone">

                </div>
                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select Courier Type</label>
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
                        <label for="example-gender-input" class="form-control-label">Select Delivery Mode</label>
                    </div>
                    <select name="delivery_mode_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($delivery_modes as $delivery_mode)
                            <option value="{{$delivery_mode->id}}">{{$delivery_mode->title}}</option>
                        @endforeach
                    </select>
                </div>
                 @php
                    $users=\App\Models\User::where('speical',1)->select('id','name')->orderBy('name')->get();
                @endphp

                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select User</label>
                    </div>
                    <select name="user_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($users as $user)
                            <option value="{{$user->id}}">{{$user->name}}</option>
                        @endforeach
                    </select>
                </div>
                <!--<div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select Sender City</label>
                    </div>
                    <select name="sender_city_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($cities as $city)
                            <option value="{{$city->id}}">{{$city->name}}</option>
                        @endforeach
                    </select>
                </div>-->
                <!--<div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select Receiver City</label>
                    </div>
                    <select name="receiver_city_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($cities as $city)
                            <option value="{{$city->id}}">{{$city->name}}</option>
                        @endforeach
                    </select>
                </div>-->
                <div>
                    <button type="submit" class="btn btn-success">Save Pricing</button>
                </div>
            </form>
        </div>
    </div>
@endsection
