@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif  
            <form action="{{ route('dashboard.support.store') }}" method="post">
                @csrf

                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select Courier ID</label>
                    </div>
                    <select id="courier_id" name="courier_id" class="form-control">
                        <option value="">Choose...</option>
                        @foreach($courier_requests as $courier_request)
                            <option value="{{$courier_request->id}}">{{$courier_request->id}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="example-title" class="form-control-label">Title</label>
                    <input class="form-control" type="text" name="title" value="{{ old('title') }}" placeholder="Support title" id="example-title" required>
                </div>

                <div class="form-group">
                    <label for="example-description" class="form-control-label">Description</label>
                    <textarea rows="20" class="form-control" type="text" name="description" placeholder="Support description" id="example-description" required>{{ old('description') }}</textarea>
                </div>
                
                
                <div>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('custom-css')
@endpush()

@push('scripts')


@endpush()