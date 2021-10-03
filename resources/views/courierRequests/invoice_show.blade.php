@extends('layout')
@section('page-content')

<div id="invoice">

    <div class="toolbar hidden-print">
        <div class="text-right">
            <button id="printInvoice" class="btn btn-info"><i class="fa fa-print"></i> Print</button>
            <a href="{{ route('dashboard.invoice.email.send',$invoice->id) }}"><button class="btn btn-info"><i class="fa fa-file-pdf-o"></i>Sent Email</button></a>
        </div>
        <hr>
    </div>
    <div class="invoice overflow-auto">
        <div style="min-width: 600px">
            <header>
                <div class="row"> 
                    <div class="col">
                        <img style="width: 250px;height: 64px" src="{{ asset('logo.svg') }}" data-holder-rendered="true" />
                    </div>
                    <div class="col company-details">
                        <h2 class="name">Holister</h2>
                        <div>Dhaka Office: Banasree, Block: G, Road: Main road, Plot No: 1</div>
                        <div>CTG Office: Rashid Master Lane, West MadarBari, Argrabad</div>
                        <div>(880) 1906-297333</div>
                        <div>info@holisterbd.com</div>
                    </div>
                </div>
            </header> 
            <main>
                <div class="row contacts">
                    <div class="col invoice-to">
                        <div class="text-gray-light">INVOICE TO:</div>
                        <h2 class="to">{{ $invoice->merchant_name }}</h2>
                        <div class="address">{{ $invoice->merchant_address }}</div>
                        <div class="phone">{{ $invoice->merchant_phone }}</div>
                        <div class="email">{{ $invoice->merchant_email }}</div>
                    </div>
                    <div class="col invoice-details">
                        <h1 class="invoice-id">INVOICE {{ $invoice->number }}</h1>
                        @if(!empty($invoice->reference_id))
                        <div>Reference ID: {{ $invoice->reference_id }}</div>
                        @endif
                        <div class="date">Date of Invoice: {{ date('d M,Y',strtotime($invoice->created_at)) }}</div>
                    </div>
                </div>
                <table border="0" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th>#Tracking ID</th>
                            <th class="text-left">Receiver</th>
                            <th class="text-right">Paid By</th>
                            <th class="text-right">COD</th>
                            <th class="text-right">Delivery Charge</th>
                            <th class="text-right">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $cods = 0;
                            $delivery_charges = 0;
                            $mercahnt_payables = 0;
                        @endphp
                        @foreach($invoice_lists as $invoice_list)
                        <tr>
                            <td class="no" style="font-size: 18px">{{ $invoice_list->tracking_id }}</td>
                            <td class="text-left" style="font-size: 16px"><h3>
                                {{ $invoice_list->receiver_name }}<br>
                                {{ $invoice_list->receiver_phone }}<br>
                                {{ $invoice_list->receiver_address }}
                            </td>
                            <td class="total" style="font-size: 18px">
                                @if($invoice_list->paid_by == "merged_with_cod")
                                    Merge With COD
                                @elseif($invoice_list->paid_by == "receiver")
                                    Receiver
                                @elseif($invoice_list->paid_by == "sender")
                                    Sender
                                @endif
                            </td>
                            <td class="unit" style="font-size: 18px">{{ $invoice_list->cod }}Tk</td>
                            <td class="qty" style="font-size: 18px">{{ $invoice_list->delivery_charge }}Tk</td>
                            <td class="total" style="font-size: 18px">{{ $invoice_list->mercahnt_payable }}Tk</td>
                        </tr>
                        @php
                            $cods = $cods + $invoice_list->cod;
                            $delivery_charges = $delivery_charges + $invoice_list->delivery_charge;
                            $mercahnt_payables = $mercahnt_payables + $invoice_list->mercahnt_payable;
                        @endphp
                        @endforeach
                        
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">COD</td>
                            <td>{{ $cods }} Tk</td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">Delivery Charge</td>
                            <td>{{ $delivery_charges }} Tk</td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td colspan="2">GRAND TOTAL</td>
                            <td>{{ $mercahnt_payables }} Tk</td>
                        </tr>
                    </tfoot>
                </table>
                <div class="thanks">Thank you!</div>
                
            </main>
            <footer>
                Invoice was created on a computer and is valid without the signature and seal.
            </footer>
        </div>
        <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
        <div></div>
    </div>
</div>



@endsection
@push('custom-css')

<style type="text/css">
    #invoice{
    padding: 30px;
}

.invoice {
    position: relative;
    background-color: #FFF;
    min-height: 680px;
    padding: 15px
}

.invoice header {
    padding: 10px 0;
    margin-bottom: 20px;
    border-bottom: 1px solid #3989c6
}

.invoice .company-details {
    text-align: right
}

.invoice .company-details .name {
    margin-top: 0;
    margin-bottom: 0
}

.invoice .contacts {
    margin-bottom: 20px
}

.invoice .invoice-to {
    text-align: left
}

.invoice .invoice-to .to {
    margin-top: 0;
    margin-bottom: 0
}

.invoice .invoice-details {
    text-align: right
}

.invoice .invoice-details .invoice-id {
    margin-top: 0;
    color: #3989c6
}

.invoice main {
    padding-bottom: 50px
}

.invoice main .thanks {
    margin-top: -100px;
    font-size: 2em;
    margin-bottom: 50px
}

.invoice main .notices {
    padding-left: 6px;
    border-left: 6px solid #3989c6
}

.invoice main .notices .notice {
    font-size: 1.2em
}

.invoice table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    margin-bottom: 20px
}

.invoice table td,.invoice table th {
    padding: 15px;
    background: #eee;
    border-bottom: 1px solid #fff
}

.invoice table th {
    white-space: nowrap;
    font-weight: 400;
    font-size: 16px
}

.invoice table td h3 {
    margin: 0;
    font-weight: 400;
    color: #3989c6;
    font-size: 1.2em
}

.invoice table .qty,.invoice table .total,.invoice table .unit {
    text-align: right;
    font-size: 1.2em
}

.invoice table .no {
    color: #fff;
    font-size: 1.6em;
    background: #3989c6
}

.invoice table .unit {
    background: #ddd
}

.invoice table .total {
    background: #3989c6;
    color: #fff
}

.invoice table tbody tr:last-child td {
    border: none
}

.invoice table tfoot td {
    background: 0 0;
    border-bottom: none;
    white-space: nowrap;
    text-align: right;
    padding: 10px 20px;
    font-size: 1.2em;
    border-top: 1px solid #aaa
}

.invoice table tfoot tr:first-child td {
    border-top: none
}

.invoice table tfoot tr:last-child td {
    color: #3989c6;
    font-size: 1.4em;
    border-top: 1px solid #3989c6
}

.invoice table tfoot tr td:first-child {
    border: none
}

.invoice footer {
    width: 100%;
    text-align: center;
    color: #777;
    border-top: 1px solid #aaa;
    padding: 8px 0
}

@media print {
    .invoice {
        font-size: 11px!important;
        overflow: hidden!important
    }

    .invoice footer {
        position: absolute;
        bottom: 10px;
        page-break-after: always
    }

    .invoice>div:last-child {
        page-break-before: always
    }
    .hidden-print{
        display:none;
    }
}
</style>


@endpush
@push('scripts')

<script type="text/javascript">
     $('#printInvoice').click(function(){
            Popup($('.invoice')[0].outerHTML);
            function Popup(data) 
            {
                window.print();
                return true;
            }
        });
</script>

@endpush
