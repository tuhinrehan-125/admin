@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Create Slider</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('dashboard.slider.update',$slider->id) }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="custom-file mb-3">
                    <input type="file" class="custom-file-input" name="image" id="customFileLang" lang="en">
                    <label class="custom-file-label" for="customFileLang">Select Image</label>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Update Slider</button>
                </div>
            </form>
        </div>
    </div>
@endsection
