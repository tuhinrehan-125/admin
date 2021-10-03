@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Courier Request Create Form</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul> 
                </div>
            @endif
            <form action="{{ route('dashboard.courier.create.store.request') }}" method="post">
                @csrf
               
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Sender City</label>
                    </div>
                    <select id="sender_city_id" name="sender_city_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($cities as $city)
                            <option value="{{$city->id}}">{{$city->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Receiver City</label>
                    </div>
                    <select id="receiver_city_id" name="receiver_city_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($cities as $city)
                            <option value="{{$city->id}}">{{$city->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Sender Area</label>
                    </div>
                    <select id="sender_area_id" name="sender_area_id" class="form-control">
                    
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Receiver Area</label>
                    </div>
                    <select id="receiver_area_id" name="receiver_area_id" class="form-control">
                        
                    </select>
                </div>

                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Hub</label>
                    </div>
                    <select id="branch_id" name="branch_id" class="form-control">
                        
                    </select>
                </div>
                @php
                   $customers = \App\Models\User::where('type','merchant')->orwhere('type','individual')->get();
                @endphp
                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select Customer</label>
                    </div>
                    <select name="customer_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($customers as $customer)
                            <option value="{{$customer->id}}">{{$customer->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Courier Type</label>
                    </div>
                    <select id="courier_type_id" name="courier_type_id" class="form-control">
                        
                    </select>
                </div>

                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Delivery Mode</label>
                    </div>
                    <select id="delivery_mode_id" name="delivery_mode_id" class="form-control">
                        
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select Package Type</label>
                    </div>
                    <select name="packaging_type_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($package_types as $package_type)
                            <option value="{{$package_type->id}}">{{$package_type->title}}</option>
                        @endforeach
                    </select>
                </div>

                
                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">Sender Address</label>
                    <input class="form-control" type="text" name="sender_address" placeholder="sender address" id="example-confirm-sender-address">
                </div>
                <div class="form-group">
                    <label for="example-receiver-address" class="form-control-label">Receiver Address</label>
                    <input class="form-control" type="text" name="receiver_address" placeholder="receiver address" id="example-receiver-address">
                </div>
                <div class="form-group">
                    <label for="example-receiver-name" class="form-control-label">Receiver Name</label>
                    <input class="form-control" type="text" name="receiver_name" placeholder="receiver name" id="example-receiver-name">
                </div>
                <div class="form-group">
                    <label for="example-receiver-phone" class="form-control-label">Receiver Phone</label>
                    <input class="form-control" type="tel" name="receiver_phone" placeholder="receiver phone" id="example-receiver-phone">
                </div>
                <div class="form-group">
                    <label for="example-note" class="form-control-label">Note</label>
                    <textarea class="form-control" name="note" id="exampleFormControlTextarea1" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="example-paid-by" class="form-control-label">Fragile</label>
                    <br>
                    <label class="custom-toggle">
                        <input type="hidden" name="fragile" value="0">
                        <input type="checkbox" name="fragile" value="1">
                        <span class="custom-toggle-slider rounded-circle"></span>
                    </label>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Payer By</label>
                    </div>
                    <select name="paid_by" class="form-control">
                        <option value="" selected>Choose...</option>
                        <option value="receiver">Receiver</option>
                        <option value="sender">Sender</option>
                        <option value="merged_with_cod">Merge With COD</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="example-paid-by" class="form-control-label">Cash On Delivery</label>
                    <br>
                    <label class="custom-toggle">
                        <input type="hidden" name="cash_on_delivery" value="0">
                        <input type="checkbox" name="cash_on_delivery" value="1">
                        <span class="custom-toggle-slider rounded-circle"></span>
                    </label>
                </div>
                <div class="form-group">
                    <label for="example-cod-amount" class="form-control-label">COD Amount</label>
                    <input class="form-control" type="number" name="cash_on_delivery_amount" placeholder="cod amount" id="example-cod-amount">
                </div>
                <div class="form-group">
                    <label for="example-approximate_weight" class="form-control-label">Approximate Weight</label>
                    <select id="approximate_weight" name="approximate_weight" class="form-control">
                        
                    </select>
                </div>
                
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Pricing</label>
                    </div>
                    <select id="pricing_id" name="pricing_id" class="form-control">
                        
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Status</label>
                    </div>
                    <select name="status_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($statuses as $status)
                            @if($status->sequence =="0")
                                <option value="{{$status->id}}">{{$status->name}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <button type="submit" class="btn btn-success">Save Courier Request</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
 
<script>
$(document).ready(function(){
    get_area_by_city();
});
function get_area_by_city(){
    var sender_city_id = $('#sender_city_id').val();
    $.post('{{ route('area.get_senderarea_by_sendercity') }}',{_token:'{{ csrf_token() }}', sender_city_id:sender_city_id}, function(data){
        $('#sender_area_id').html(null);
        $('#sender_area_id').append($('<option>', {
                value: "",
                text: "Select Area"
            }));
        for (var i = 0; i < data.length; i++) {
            $('#sender_area_id').append($('<option>', {
                value: data[i].id,
                text: data[i].name
            }));
            $('.demo-select2').select();
        }
    });
  }
  function get_receiverarea_by_receivercity(){
    var receiver_city_id = $('#receiver_city_id').val();
    $.post('{{ route('area.get_receiverarea_by_receivercity') }}',{_token:'{{ csrf_token() }}', receiver_city_id:receiver_city_id}, function(data){
        $('#receiver_area_id').html(null);
        $('#receiver_area_id').append($('<option>', {
                value: "",
                text: "Select Area"
            }));
        for (var i = 0; i < data.length; i++) {
            $('#receiver_area_id').append($('<option>', {
                value: data[i].id,
                text: data[i].name
            }));
            $('.demo-select2').select();
        }
    });
  }
  function get_hub_by_area(){
    var sender_area_id = $('#sender_area_id').val();
    $.post('{{ route('area.get_hub_by_area') }}',{_token:'{{ csrf_token() }}', sender_area_id:sender_area_id}, function(data){
        $('#branch_id').html(null);
        $('#branch_id').append($('<option>', {
                value: "",
                text: "Select Area"
            }));        
        for (var i = 0; i < data.length; i++) {
            $('#branch_id').append($('<option>', {
                value: data[i].id,
                text: data[i].name
            }));
            $('.demo-select2').select();
        }
    });
  }

  function get_delivery_by_city(){
    var sender_city_id = $('#sender_city_id').val();
    var receiver_city_id = $('#receiver_city_id').val();
    $.post('{{ route('deliver.get_delivermode_by_city') }}',{_token:'{{ csrf_token() }}', sender_city_id:sender_city_id,receiver_city_id:receiver_city_id}, function(data){
        $('#courier_type_id').html(null);
         $('#courier_type_id').append($('<option>', {
                value: "",
                text: "Select Delivery Mode" 
            }));
        
        for (var i = 0; i < data.length; i++) {
            $('#courier_type_id').append($('<option>', {
                value: data[i].id,
                text: data[i].title
            }));
            $('.demo-select2').select();
        }
    });
  }


  function get_deliverytype_by_deliverymode(){
    var courier_type_id = $('#courier_type_id').val();
    $.post('{{ route('deliver.get_deliverytype_by_deliverymode') }}',{_token:'{{ csrf_token() }}', courier_type_id:courier_type_id}, function(data){
        
        $('#delivery_mode_id').html(null);
         $('#delivery_mode_id').append($('<option>', {
                value: "",
                text: "Select Delivery Type" 
            }));
        for (var i = 0; i < data.length; i++) {
            $('#delivery_mode_id').append($('<option>', {
                value: data[i].id,
                text: data[i].title
            }));
            $('.demo-select2').select();
        }
    });
  }

  function get_weight_by_all(){
    var courier_type_id = $('#courier_type_id').val();
    var delivery_mode_id = $('#delivery_mode_id').val();
    $.post('{{ route('weight.get_weight_by_all') }}',{_token:'{{ csrf_token() }}', courier_type_id:courier_type_id,delivery_mode_id:delivery_mode_id}, function(data){
        
        $('#approximate_weight').html(null);
        $('#approximate_weight').append($('<option>', {
                value: "",
                text: "Select Approximate Weight" 
            }));
        for (var i = 0; i < data.length; i++) {
            $('#approximate_weight').append($('<option>', {
                value: data[i].id,
                text: data[i].min_weight +' - '+data[i].max_weight
            }));
            $('.demo-select2').select();
        }
    });
  }

  function get_price_by_weight(){
    var approximate_weight = $('#approximate_weight').val();
    $.post('{{ route('price.get_price_by_weight') }}',{_token:'{{ csrf_token() }}', approximate_weight:approximate_weight}, function(data){
        $('#pricing_id').html(null);
        for (var i = 0; i < data.length; i++) {
            $('#pricing_id').append($('<option>', {
                value: data[i].id,
                text: data[i].price
            }));
            $('.demo-select2').select();
        }
    });
  }

  $('#sender_city_id').on('change', function() {
      get_area_by_city();
  });
  $('#sender_area_id').on('change', function() {
      get_hub_by_area();
  });
  $('#receiver_city_id').on('change', function() {
      get_receiverarea_by_receivercity();
      get_delivery_by_city();
  });
  $('#courier_type_id').on('change', function() { 
      get_deliverytype_by_deliverymode();
      get_weight_by_all();
  });

  $('#delivery_mode_id').on('change', function() { 
      get_weight_by_all();
  });

  $('#approximate_weight').on('change', function() { 
      get_price_by_weight();
  });
</script>


@endpush()