@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Courier Request Hub Transfer</p>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul> 
                </div>
            @endif

            Pickup Hub = {{ !empty($courier->branch->name)?$courier->branch->name:'Not Available' }} &nbsp; Time: {{ date('d M, Y h:i A',strtotime($courier->created_at)) }}<br>
            @foreach($hub_transfers as $hub_transfer)
                @if($hub_transfer->hub_status == "2") 
                    Transit Hub = {{ !empty($hub_transfer->hub->name)?$hub_transfer->hub->name:'Not Available' }} &nbsp; Time: {{ date('d M, Y h:i A',strtotime($hub_transfer->created_at)) }}
                @else
                    Delivery Hub = {{ !empty($hub_transfer->hub->name)?$hub_transfer->hub->name:'Not Available' }} &nbsp; Time: {{ date('d M, Y h:i A',strtotime($hub_transfer->created_at)) }}
                @endif
                <br>
            @endforeach
            <br>
            
            @if($courier->hub_status == "1")
                Already Sent to Delivery Hub
            @else
            <form action="{{ route('courier_request_hub_transfer_store',$courier->id) }}" method="post">
                @csrf
                <div class="form-group">
                    <div>
                        <label for="example-gender-input" class="form-control-label">Select Hub</label>
                    </div>
                    <select name="hub_id" class="form-control">
                        <option value="" selected>Choose...</option>
                        @foreach($hubs as $hub)
                            <option value="{{$hub->id}}">{{$hub->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <div>
                        <label for="example-text-type" class="form-control-label">Hub Transfer Status</label>
                    </div>
                    <select name="hub_status" class="form-control">
                        <option value="" selected>Choose...</option>
                        <option value="1">Delivery Hub</option>
                        <option value="2">Transit Hub</option>
                    </select>
                </div>
                
                <div>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
            @endif
        </div>
    </div>
@endsection

