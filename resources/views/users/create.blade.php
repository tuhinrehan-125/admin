@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body"> 
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">User Create Form</p>
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
            <form action="{{ route('dashboard.user.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Name</label>
                    <input class="form-control" type="text" name="name" placeholder="John Snow" id="example-text-input">
                </div>
                <div class="form-group">
                    <label for="example-email-input" class="form-control-label">Email</label>
                    <input class="form-control" type="email" name="email" placeholder="argon@example.com" id="example-email-input">
                </div>
                <div class="form-group">
                    <label for="example-tel-input" class="form-control-label">Phone</label>
                    <input class="form-control" type="tel" name="phone" placeholder="01800000000" id="example-tel-input">
                </div>
                <div class="custom-file mb-3">
                    <input type="file" class="custom-file-input" name="profile_picture" id="customFileLang" lang="en">
                    <label class="custom-file-label" for="customFileLang">Select Image</label>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select Gender</label>
                    </div>
                    <select name="gender" class="form-control">
                        <option value="" selected>Choose...</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Type</label>
                    </div>
                    <select name="type" class="form-control">
                        <option value="" selected>Choose...</option>
                        <option value="individual">Individual</option>
                        <option value="merchant">Merchant</option>
                        <!--<option value="delivery_rider">Delivery Rider</option>
                        <option value="pickup_rider">Pickup Rider</option>-->
                        <option value="rider">Rider</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                        <option value="account">Accountant</option>
                        <option value="care">Customer Support</option>
                        <option value="hr">HR</option>
                        <option value="marketing">Marketing & KAM</option>
                        <option value="bkash_agent">Bkash Agent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="example-confirm-password-input" class="form-control-label">Password</label>
                    <input class="form-control" type="password" name="password" placeholder="password" id="example-password-input">
                </div>
                <div class="form-group">
                    <label for="example-password-input" class="form-control-label">Confirm Password</label>
                    <input class="form-control" type="password" name="password_confirmation" placeholder="password" id="example-confirm-password-input">
                </div>
                <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Bkash Number</label>
                    <input class="form-control" type="text" name="bkash_no" placeholder="Bkash Number" id="example-text-input">
                </div>
                <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Nagad Number</label>
                    <input class="form-control" type="text" name="nagad_no" placeholder="Nagad Number" id="example-text-input">
                </div>
                <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Rocket Number</label>
                    <input class="form-control" type="text" name="rocket_no" placeholder="Rocket Number" id="example-text-input">
                </div>
                <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Bank Account Number</label>
                    <input class="form-control" type="text" name="bank_ac_no" placeholder="Bank Account Number" id="example-text-input">
                </div>
                <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Bank Account Holder Name</label>
                    <input class="form-control" type="text" name="bankAC_name" placeholder="Bank Account Holder Name" id="example-text-input">
                </div>
                <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Bank Name</label>
                    <input class="form-control" type="text" name="bank_name" placeholder="Bank Name" id="example-text-input">
                </div>
                <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Bank Branch</label>
                    <input class="form-control" type="text" name="bank_branch" placeholder="Bank Branch" id="example-text-input">
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Preferred Method</label>
                    </div>
                    <select name="preferred_method" class="form-control">
                        <option value="" selected>Choose...</option>
                        <option value="bank">bank</option>
                        <option value="bkash">bkash</option>
                        <option value="nagad">nagad</option>
                        <option value="rocket">rocket</option>
                        <option value="cash">cash</option>
                    </select>
                </div>
                @php
                $citis = \App\Models\City::all();
                @endphp
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Merchant Shop City</label>
                    </div>
                    <select id="city_id" name="merchant_shop_city" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($citis as $city)
                            <option value="{{$city->id}}">{{$city->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Merchant Shop Area</label>
                    </div>
                    <select id="area_id" name="merchant_shop_area" class="form-control">
                        
                    </select>
                </div>
                <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Shop Address</label>
                    <input class="form-control" type="text" name="merchant_shop_address" placeholder="Shop Address" id="example-text-input">
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Rider City</label>
                    </div>
                    <select id="cities" name="rider_city" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($citis as $city)
                            <option value="{{$city->id}}">{{$city->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Rider Hub Assign</label>
                    </div>
                    <select id="rider_hub" name="rider_hub" class="form-control">  
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Rider Area Assign</label>
                    </div>
                    <select multiple="true" id="rider_area" name="rider_area[]" class="form-control mul-select">  
                    </select>
                </div>
                <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Pickup Rider Commission</label>
                    <input class="form-control" type="text" name="pickup_rider_commission" placeholder="Pickup Rider Commission" id="example-text-input">
                </div>
                <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Delivery Rider Commission</label>
                    <input class="form-control" type="text" name="delivery_rider_commission" placeholder="Delivery Rider Commission" id="example-text-input">
                </div>
                <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Pickup Agent Commission(%)</label>
                    <input class="form-control" type="text" name="pickup_agent_commission" placeholder="Pickup Agent Commission(%)" id="example-text-input">
                </div>
                <div class="form-group">
                    <label for="example-text-input" class="form-control-label">Delivery Agent Commission(%)</label>
                    <input class="form-control" type="text" name="delivery_agent_commission" placeholder="Delivery Agent Commission(%)" id="example-text-input">
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Save </button>
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
<script>
$(document).ready(function(){
    get_area_by_hub();
});
  function get_hub_by_city(){
    var cities = $('#cities').val();
    $.post('{{ route('hub.get_hub_by_city') }}',{_token:'{{ csrf_token() }}', cities:cities}, function(data){
        $('#rider_hub').html(null);
        $('#rider_hub').append($('<option>', {
                value: "",
                text: "Select Hub"
            }));
        for (var i = 0; i < data.length; i++) {
            $('#rider_hub').append($('<option>', {
                value: data[i].id,
                text: data[i].name
            }));
            $('.demo-select2').select();
        }
    });
  }
  
  function get_area_by_hub(){
    var rider_hub = $('#rider_hub').val();
    $.post('{{ route('area.get_area_by_hub') }}',{_token:'{{ csrf_token() }}', rider_hub:rider_hub}, function(data){
        $('#rider_area').html(null);
        $('#rider_area').append($('<option>', {
                value: "",
                text: "Select Area"
            }));        
        for (var i = 0; i < data.length; i++) {
            $('#rider_area').append($('<option>', {
                value: data[i].id,
                text: data[i].name
            }));
            $('.demo-select2').select();
        }
    });
  }

  $('#cities').on('change', function() {
      get_hub_by_city();
  });
  $('#rider_hub').on('change', function() {
      get_area_by_hub();
  });
      
</script>
@endpush()
