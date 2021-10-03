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
            <form action="{{ route('dashboard.sliders.collection.update',$slider_collection->id) }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="example-title" class="form-control-label">Slider Collection Title</label>
                    <input class="form-control" type="text" name="title" placeholder="courier type" id="example-title" value="{{$slider_collection->title}}">
                </div>
{{--                <div class="custom-file mb-3">--}}
{{--                    <input type="file" class="custom-file-input" name="images[]" id="customFileLang" lang="en">--}}
{{--                    <label class="custom-file-label" for="customFileLang">Select Image</label>--}}
{{--                </div>--}}
{{--                <div class="custom-file mb-3">--}}
{{--                    <input type="file" class="custom-file-input" name="images[]" id="customFileLang" lang="en">--}}
{{--                    <label class="custom-file-label" for="customFileLang">Select Image</label>--}}
{{--                </div>--}}
{{--                <div class="custom-file mb-3">--}}
{{--                    <input type="file" class="custom-file-input" name="images[]" id="customFileLang" lang="en">--}}
{{--                    <label class="custom-file-label" for="customFileLang">Select Image</label>--}}
{{--                </div>--}}
                <div class="d-flex flex-wrap" style="column-gap: 1.3em; margin-bottom: 2em;">
                    @foreach($sliders as $slider)
                        <div>
                            <img src="{{asset($slider->image)}}" width="170px">
                            <!-- <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="images[]" value="{{$slider->id}}"
                                       id="flexCheckDefault" {{in_array($slider->id,$slider_id)?'checked':''}}>
                                <label class="form-check-label" for="flexCheckDefault">
                                    Select
                                </label>
                            </div> -->
                        </div>
                    @endforeach
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Update Slider Collection</button>
                </div>
            </form>
        </div>
    </div>
@endsection
