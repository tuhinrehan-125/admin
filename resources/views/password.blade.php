@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Password Change</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div> 
            @endif
            @if(session('message'))
                <div class="alert alert-info">
                    {{session('message')}}
                </div>
            @endif
            <form action="{{ route('dashboard.password.changing') }}" method="post">
                @csrf

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">Email</label>
                    <input class="form-control" type="email" placeholder="Password" id="example-confirm-sender-address" value="{{ Auth::user()->email }}" readonly>
                </div>
                
                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">Password</label>
                    <input class="form-control" type="password" name="password" placeholder="Password" id="example-confirm-sender-address" required>
                </div>
                <div class="form-group">
                    <label for="example-receiver-address" class="form-control-label">Confirm Password</label>
                    <input class="form-control" type="password" name="password_confirmation" placeholder="Confirm Password" id="example-receiver-address" required>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')



@endpush()