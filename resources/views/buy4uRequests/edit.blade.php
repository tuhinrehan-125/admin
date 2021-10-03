@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Buy4u Request Update Form</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach 
                    </ul>
                </div>
            @endif
            <form action="{{ route('dashboard.buy4u.request.update',$buy4u_request->id) }}" method="post">
                @csrf
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Buy4u Type</label>
                    </div>
                    <select name="buy4u_type_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($buy4u_types as $buy4u_type)
                            <option value="{{$buy4u_type->id}}" {{$buy4u_request->buy4u_type_id==$buy4u_type->id?'selected':''}}>{{$buy4u_type->title}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select City</label>
                    </div>
                    <select id="city_id" name="city_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($cities as $city)
                            <option value="{{$city->id}}" {{$buy4u_request->city_id == $city->id?'selected':''}}>{{$city->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Area</label>
                    </div>
                    <select id="area_id" name="area_id" class="form-control">
                        <!-- <option value="" selected>Choose...</option>
                        @foreach($areas as $area)
                            <option value="{{$area->id}}" {{$buy4u_request->area_id == $area->id?'selected':''}}>{{$area->name}}</option>
                        @endforeach -->
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Hub</label>
                    </div>
                    <select id="branch_id" name="branch_id" class="form-control" demo-select2-placeholder>
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select Rider</label>
                    </div>
                    <select name="rider_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($riders as $rider)
                            <option value="{{$rider->id}}" {{$buy4u_request->rider_id==$rider->id?'selected':''}}>{{$rider->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">Address</label>
                    <input class="form-control" type="text" name="address" placeholder="address" id="example-confirm-sender-address" value="{{$buy4u_request->address}}">
                </div>
                <div class="form-group">
                    <label for="example-receiver-address" class="form-control-label">Preferred Shop Name</label>
                    <input class="form-control" type="text" name="preferred_shop_name" placeholder="preferred shop name" id="example-receiver-address" value="{{$buy4u_request->preferred_shop_name}}">
                </div>
                <div class="form-group">
                    <label for="example-shop-address" class="form-control-label">Preferred Shop Address</label>
                    <input class="form-control" type="text" name="preferred_shop_address" placeholder="preferred shop address" id="example-shop-address" value="{{$buy4u_request->preferred_shop_address}}">
                </div>
                <div class="form-group">
                    <label for="example-shop-note" class="form-control-label">Note</label>
                    <input class="form-control" type="text" name="note" placeholder="note.." id="example-shop-note" value="{{$buy4u_request->note}}">
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Status</label>
                    </div>
                    <select name="status_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($statuses as $status)
                            <option value="{{$status->id}}" {{$buy4u_request->status_id==$status->id?'selected':''}}>{{$status->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Pricing</label>
                    </div>
                    <select name="pricing_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($pricings as $pricing)
                            <option value="{{$pricing->id}}" {{$buy4u_request->pricing_id==$pricing->id?'selected':''}}>{{$pricing->price}}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Update Buy4u Request</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
<script>
$(document).ready(function(){
    get_area_by_city();
    get_hub_by_area();
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
            $("#area_id > option").each(function() {
              if(this.value == '{{$buy4u_request->area_id}}'){
                  $("#area_id").val(this.value).change();
              }
           });
            $('.demo-select2').select();
        }
    });
  }
  function get_hub_by_area(){
    var area_id = $('#area_id').val();
    $.post('{{ route('hub.get_hub_by_area') }}',{_token:'{{ csrf_token() }}', area_id:area_id}, function(data){
        $('#branch_id').html(null);
        for (var i = 0; i < data.length; i++) {
            $('#branch_id').append($('<option>', {
                value: data[i].id,
                text: data[i].name
            }));
            $("#branch_id > option").each(function() {
              if(this.value == '{{$buy4u_request->branch_id}}'){
                  $("#branch_id").val(this.value).change();
              }
           });
            $('.demo-select2').select();
        }
    });
  }
  $('#city_id').on('change', function() {
      get_area_by_city();
  });
  $('#area_id').on('change', function() {
      get_hub_by_area();
  });
  
</script>
@endpush()