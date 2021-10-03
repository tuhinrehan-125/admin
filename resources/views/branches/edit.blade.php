@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Update Hub</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('branch.dashboard.update',$branch->id) }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="example-name" class="form-control-label">Hub Name</label>
                    <input class="form-control" type="text" name="name" placeholder="Hub name" id="example-name" value="{{$branch->name}}">
                </div>
                <div class="form-group">
                    <label for="example-email" class="form-control-label">Hub Email</label>
                    <input class="form-control" type="email" name="email" placeholder="Hub email" id="example-email" value="{{$branch->email}}">
                </div>
                <div class="form-group">
                    <label for="example-phone" class="form-control-label">Hub Phone</label>
                    <input class="form-control" type="tel" name="phone" placeholder="Hub phone" id="example-phone" value="{{$branch->phone}}">
                </div>
                <div class="form-group">
                    <label for="example-address" class="form-control-label">Hub Address</label>
                    <input class="form-control" type="text" name="address" placeholder="Hub address" id="example-address" value="{{$branch->address}}">
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select city</label>
                    </div>
                    <select id="city_id" name="city_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($cities as $city)
                            <option value="{{$city->id}}" {{$city->id==$branch->city_id?'selected':''}}>{{$city->name}}</option>
                        @endforeach
                    </select>
                </div>
                @php
                  if(isset($branch)){
                    $areaes = explode(',', $branch->area_id);
                  }
                @endphp 
                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select Area</label>
                    </div>
                    <select multiple="true" id="area_id" name="area_id[]" class="form-control mul-select">
                        @foreach($areas as $area)
                            <option @if(isset($branch)) @if(in_array($area->id, $areaes)) selected @endif @endif value="{{ $area->id }}">{{$area->name}}</option>
                        @endforeach
                        
                    </select>
                </div>
                @php
                $managers = \App\Models\User::where('type','manager')->get();
                @endphp

                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Supervisior</label>
                    </div>
                    <select id="supervisior_id" name="supervisior_id" class="form-control">
                        <option value="">Choose...</option>
                        @foreach($managers as $manager)
                            <option {{$manager->id==$branch->supervisior_id?'selected':''}} value="{{$manager->id}}">{{$manager->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="example-agent" class="form-control-label">Agent</label>
                    <br>
                    <label class="custom-toggle">
                        <input type="hidden" name="is_agent" value="0" >
                        <input type="checkbox" name="is_agent" value="1" {{$branch->is_agent?'checked':''}}>
                        <span class="custom-toggle-slider rounded-circle"></span>
                    </label>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Update Hub</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('custom-css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css">
@endpush()
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $(".mul-select").select2({
        placeholder: "select area", //placeholder
        tags: true,
        tokenSeparators: ['/',',',';'," "] 
    });
})
</script> 


@endpush()