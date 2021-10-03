@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Add Hub</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif  
            <form action="{{ route('branch.dashboard.store.create') }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="example-name" class="form-control-label">Hub Name</label>
                    <input class="form-control" type="text" name="name" placeholder="Hub name" id="example-name">
                </div>
                <div class="form-group">
                    <label for="example-email" class="form-control-label">Hub Email</label>
                    <input class="form-control" type="email" name="email" placeholder="Hub email" id="example-email">
                </div>
                <div class="form-group">
                    <label for="example-phone" class="form-control-label">Hub Phone</label>
                    <input class="form-control" type="tel" name="phone" placeholder="Hub phone" id="example-phone">
                </div>
                <div class="form-group">
                    <label for="example-address" class="form-control-label">Hub Address</label>
                    <input class="form-control" type="text" name="address" placeholder="Hub address" id="example-address">
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select city</label>
                    </div>
                    <select id="city_id" name="city_id" class="form-control">
                        <option value="">Choose...</option>
                        @foreach($cities as $city)
                            <option value="{{$city->id}}">{{$city->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select Area</label>
                    </div>
                    <select multiple="true" id="area_id" name="area_id[]" class="form-control mul-select" demo-select2-placeholder>
                        
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
                            <option value="{{$manager->id}}">{{$manager->name}}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="example-agent" class="form-control-label">Agent</label>
                    <br>
                    <label class="custom-toggle">
                        <input type="hidden" name="is_agent" value="0">
                        <input type="checkbox" name="is_agent" value="1">
                        <span class="custom-toggle-slider rounded-circle"></span>
                    </label>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Save</button>
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
<script>
$(document).ready(function(){
    get_area_by_city();
});
function get_area_by_city(){
    var city_id = $('#city_id').val();
    $.post('{{ route('area.get_area_by_city') }}',{_token:'{{ csrf_token() }}', city_id:city_id}, function(data){
        $('#area_id').html(null);
        $('#area_id').append($('<option>', {
                value: "",
                text: "Select Area"
            }));
        for (var i = 0; i < data.length; i++) {
            $('#area_id').append($('<option>', {
                value: data[i].id,
                text: data[i].name
            }));
            $('.demo-select2').select();
        }
    });
  }
  $('#city_id').on('change', function() {
      get_area_by_city();
  });
</script>
@endpush()