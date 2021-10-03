@extends('layout')
@section('page-content')
    <div class="card mt-5" style="width: 100%;">
        <div class="card-body">
            <p class="text-bold" style="background:#dee2e6; padding: 1.2em;">Dashboard</p>
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
            <form action="{{ route('dashboard.form.entry.update',$dashboard->id) }}" method="post">
                @csrf

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">TODAYS PARCEL ENTRY</label>
                    <input class="form-control" type="text" placeholder="TODAYS PARCEL ENTRY" name="todays_parcel_entry" id="example-confirm-sender-address" value="{{ $dashboard->todays_parcel_entry }}">
                </div>

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">TODAYS CANCEL PARCEL</label>
                    <input class="form-control" type="text" placeholder="TODAYS CANCEL PARCEL" name="todays_cancel_parcel" id="example-confirm-sender-address" value="{{ $dashboard->todays_cancel_parcel }}">
                </div>

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">TOTAL PARCEL ENTRY TILL NOW</label>
                    <input class="form-control" type="text" placeholder="TOTAL PARCEL ENTRY TILL NOW" name="total_parcel_entry_till_now" id="example-confirm-sender-address" value="{{ $dashboard->total_parcel_entry_till_now }}">
                </div>

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">TOTAL CANCEL PARCEL TILL NOW</label>
                    <input class="form-control" type="text" placeholder="TOTAL CANCEL PARCEL TILL NOW" name="total_cancel_parcel_till_now" id="example-confirm-sender-address" value="{{ $dashboard->total_cancel_parcel_till_now }}">
                </div>
                
                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">TOTAL DELIVERED TODAY</label>
                    <input class="form-control" type="text" placeholder="TOTAL DELIVERED TODAY" name="total_delivered_today" id="example-confirm-sender-address" value="{{ $dashboard->total_delivered_today }}">
                </div>

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">TOTAL DELIVERED TILL NOW</label>
                    <input class="form-control" type="text" placeholder="TOTAL DELIVERED TILL NOW" name="total_delivered_till_now" id="example-confirm-sender-address" value="{{ $dashboard->total_delivered_till_now }}">
                </div>

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">DELIVERY CHARGE ENTERED TODAY</label>
                    <input class="form-control" type="text" placeholder="DELIVERY CHARGE ENTERED TODAY" name="delivery_charge_entered_today" id="example-confirm-sender-address" value="{{ $dashboard->delivery_charge_entered_today }}">
                </div>

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">DELIVERY CHARGE TOTAL RECEIVABLE</label>
                    <input class="form-control" type="text" placeholder="DELIVERY CHARGE TOTAL RECEIVABLE" name="delivery_charge_total_receivable" id="example-confirm-sender-address" value="{{ $dashboard->delivery_charge_total_receivable }}">
                </div>

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">DELIVERY CHARGE COLLECTED TODAY</label>
                    <input class="form-control" type="text" placeholder="DELIVERY CHARGE COLLECTED TODAY" name="delivery_charge_collected_today" id="example-confirm-sender-address" value="{{ $dashboard->delivery_charge_collected_today }}">
                </div>

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">DELIVERY CHARGE DUE TODAY</label>
                    <input class="form-control" type="text" placeholder="DELIVERY CHARGE DUE TODAY" name="delivery_charge_due_today" id="example-confirm-sender-address" value="{{ $dashboard->delivery_charge_due_today }}">
                </div>

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">DELIVERY CHARGE COLLECTED TILL NOW</label>
                    <input class="form-control" type="text" placeholder="DELIVERY CHARGE COLLECTED TILL NOW" name="delivery_charge_collected_till_now" id="example-confirm-sender-address" value="{{ $dashboard->delivery_charge_collected_till_now }}">
                </div>

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">COD ENTRY TODAY</label>
                    <input class="form-control" type="text" placeholder="COD ENTRY TODAY" name="cod_entry_today" id="example-confirm-sender-address" value="{{ $dashboard->cod_entry_today }}">
                </div>

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">COD COLLECTED RECEIVABLE BY MERCHANT</label>
                    <input class="form-control" type="text" placeholder="COD COLLECTED RECEIVABLE BY MERCHANT" name="cod_collected_receivable_by_merchant" id="example-confirm-sender-address" value="{{ $dashboard->cod_collected_receivable_by_merchant }}">
                </div>

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">TOTAL COD PAID TO MERCHANT</label>
                    <input class="form-control" type="text" placeholder="TOTAL COD PAID TO MERCHANT" name="total_cod_paid_to_merchant" id="example-confirm-sender-address" value="{{ $dashboard->total_cod_paid_to_merchant }}">
                </div>

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">TOTAL COD DUE</label>
                    <input class="form-control" type="text" placeholder="TOTAL COD DUE" name="total_cod_due" id="example-confirm-sender-address" value="{{ $dashboard->total_cod_due }}">
                </div>

                <div class="form-group">
                    <label for="example-confirm-sender-address" class="form-control-label">COD COLLECTED TILL NOW</label>
                    <input class="form-control" type="text" placeholder="COD COLLECTED TILL NOW" name="cod_collected_till_now" id="example-confirm-sender-address" value="{{ $dashboard->cod_collected_till_now }}">
                </div>


                
                <div>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')



@endpush()