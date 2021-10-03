@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Status Create Form</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('dashboard.status.store.create') }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="example-title" class="form-control-label">Status Name</label>
                    <input class="form-control" type="text" name="name" placeholder="name" id="example-title">
                </div>
                <div class="form-group">
                    <label for="example-title" class="form-control-label">Status Sequence</label>
                    <input class="form-control" type="number" name="sequence" placeholder="Sequence" id="example-title">
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Save Status</button>
                </div>
            </form>
        </div>
    </div>
@endsection
