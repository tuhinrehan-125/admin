@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Courier Request Update Form</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul> 
                </div>
            @endif
            <form action="{{ route('dashboard.courier.request.update',$courier_request->id) }}" method="post">
                @csrf
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Courier Type</label>
                    </div>
                    <select name="courier_type_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($courier_types as $courier_type)
                            <option value="{{$courier_type->id}}" {{$courier_type->id==$courier_request->courier_type_id?'selected':''}}>{{$courier_type->title}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Sender City</label>
                    </div>
                    <select id="sender_city_id" name="sender_city_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($cities as $city)
                            <option value="{{$city->id}}" {{$city->id==$courier_request->sender_city_id?'selected':''}}>{{$city->name}}</option>
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
                            <option value="{{$city->id}}" {{$city->id==$courier_request->receiver_city_id?'selected':''}}>{{$city->name}}</option>
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
                            <option value="{{$customer->id}}" {{$customer->id==$courier_request->customer_id?'selected':''}}>{{$customer->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select Package Type</label>
                    </div>
                    <select name="packaging_type_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($package_types as $package_type)
                            <option value="{{$package_type->id}}" {{$package_type->id==$courier_request->packaging_type_id?'selected':''}}>{{$package_type->title}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Delivery Mode</label>
                    </div>
                    <select name="delivery_mode_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($delivery_modes as $delivery_mode)
                            <option value="{{$delivery_mode->id}}" {{$delivery_mode->id==$courier_request->delivery_mode_id?'selected':''}}>{{$delivery_mode->title}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">Sender Address</label>
                    <input class="form-control" type="text" name="sender_address" placeholder="sender address" id="example-confirm-sender-address" value="{{$courier_request->sender_address}}">
                </div>
                <div class="form-group">
                    <label for="example-receiver-address" class="form-control-label">Receiver Address</label>
                    <input class="form-control" type="text" name="receiver_address" placeholder="receiver address" id="example-receiver-address" value="{{$courier_request->receiver_address}}">
                </div>
                <div class="form-group">
                    <label for="example-receiver-name" class="form-control-label">Receiver Name</label>
                    <input class="form-control" type="text" name="receiver_name" placeholder="receiver name" id="example-receiver-name" value="{{$courier_request->receiver_name}}">
                </div>
                <div class="form-group">
                    <label for="example-receiver-phone" class="form-control-label">Receiver Phone</label>
                    <input class="form-control" type="tel" name="receiver_phone" placeholder="receiver phone" id="example-receiver-phone" value="{{$courier_request->receiver_phone}}">
                </div>
                <div class="form-group">
                    <label for="example-note" class="form-control-label">Note</label>
                    <textarea class="form-control" name="note" id="exampleFormControlTextarea1" rows="3">{{$courier_request->note}}</textarea>
                </div>
                <div class="form-group">
                    <label for="example-paid-by" class="form-control-label">Fragile</label>
                    <br>
                    <label class="custom-toggle">
                        <input type="hidden" name="fragile" value="0" {{$courier_request->fragile==0?'checked':''}}>
                        <input type="checkbox" name="fragile" value="1" {{$courier_request->fragile==1?'checked':''}}>
                        <span class="custom-toggle-slider rounded-circle"></span>
                    </label>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Payer By</label>
                    </div>
                    <select name="paid_by" class="form-control">
                        <option value="" selected>Choose...</option>
                        <option value="receiver" {{$courier_request->paid_by=='receiver'?'selected':''}}>Receiver</option>
                        <option value="sender" {{$courier_request->paid_by=='sender'?'selected':''}}>Sender</option>
                        <option value="merged_with_cod" {{$courier_request->paid_by=='merged_with_cod'?'selected':''}}>Merge With COD</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="example-paid-by" class="form-control-label">Cash On Delivery</label>
                    <br>
                    <label class="custom-toggle">
                        <input type="hidden" name="cash_on_delivery" value="0" {{$courier_request->cash_on_delivery==0?'checked':''}}>
                        <input type="checkbox" name="cash_on_delivery" value="1" {{$courier_request->cash_on_delivery==1?'checked':''}}>
                        <span class="custom-toggle-slider rounded-circle"></span>
                    </label>
                </div>
                <div class="form-group">
                    <label for="example-cod-amount" class="form-control-label">COD Amount</label>
                    <input class="form-control" type="number" name="cash_on_delivery_amount" placeholder="cod amount" id="example-cod-amount" value="{{$courier_request->cash_on_delivery_amount}}">
                </div>
                <div class="form-group">
                    <label for="example-approximate_weight" class="form-control-label">Approximate Weight</label>
                    <input class="form-control" type="number" name="approximate_weight" placeholder="Approximate Weight" id="example-approximate_weight" value="{{$courier_request->approximate_weight}}">
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Select Status</label>
                    </div>
                    <select name="status_id" class="form-control" disabled="true">
                        <option value="" selected>Choose...</option>
                        @foreach($statuses as $status)
                            <option value="{{$status->id}}" {{$status->id==$courier_request->status_id?'selected':''}}>{{$status->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Assign Rider</label>
                    </div>
                    <select name="rider_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($riders as $rider)
                            <option value="{{$rider->id}}" {{$rider->id==$courier_request->rider_id ? 'selected' : ''}}>{{$rider->name}}</option>
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
                            <option value="{{$pricing->id}}" {{$pricing->id==$courier_request->pricing_id?'selected':''}}>{{$pricing->price}}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">Update Courier Request</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
 
<script>
$(document).ready(function(){
    get_area_by_city();
    get_receiverarea_by_receivercity();
    get_hub_by_area();
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
            $("#sender_area_id > option").each(function() {
              if(this.value == '{{$courier_request->sender_area_id}}'){
                  $("#sender_area_id").val(this.value).change();
              }
           });
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
           $("#receiver_area_id > option").each(function() {
              if(this.value == '{{$courier_request->receiver_area_id}}'){
                  $("#receiver_area_id").val(this.value).change();
              }
           });
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
            $("#branch_id > option").each(function() {
              if(this.value == '{{$courier_request->branch_id}}'){
                  $("#branch_id").val(this.value).change();
              }
           });
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
  });
</script>


@endpush()