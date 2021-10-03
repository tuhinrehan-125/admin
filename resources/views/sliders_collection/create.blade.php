@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Create Slider Collection</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach 
                    </ul>
                </div>
            @endif
            <form action="{{ route('dashboard.sliders.collection.store.create') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="example-title" class="form-control-label">Slider Collection Title</label>
                    <input class="form-control" type="text" name="title" placeholder="slider collection title" id="example-title">
                </div>
                <div class="custom-file mb-3">
                    <input type="file" class="custom-file-input" name="images[]" id="customFileLang" lang="en">
                    <label class="custom-file-label" for="customFileLang">Select Image</label>
                </div>
                <div class="custom-file mb-3">
                    <input type="file" class="custom-file-input" name="images[]" id="customFileLang" lang="en">
                    <label class="custom-file-label" for="customFileLang">Select Image</label>
                </div>
                <div class="custom-file mb-3">
                    <input type="file" class="custom-file-input" name="images[]" id="customFileLang" lang="en">
                    <label class="custom-file-label" for="customFileLang">Select Image</label>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Save Slider Collection</button>
                </div>
            </form>
        </div>
    </div>
@endsection
