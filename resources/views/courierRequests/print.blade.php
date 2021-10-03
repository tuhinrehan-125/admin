<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/brand/favicon.png') }}" />
    <title>{{config('app.name', 'Holister')}}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">

    <style type="text/css">
        * {
            font-size: 12px; 
            line-height: 20px;
            font-family: 'Ubuntu', sans-serif;
            text-transform: capitalize;
        }
        .btn {
            padding: 7px 10px;
            text-decoration: none;
            border: none;
            display: block;
            text-align: center;
            margin: 7px;
            cursor:pointer; 
        } 

        .btn-info {
            background-color: #999;
            color: #FFF;
        }

        .btn-primary {
            background-color: #6449e7;
            color: #FFF;
            width: 100%;
        }
        td,
        th,
        tr,
        table {
            border-collapse: collapse;
        }
        tr {border-bottom: 1px dotted #ddd;}
        td,th {padding: 7px 0;width: 20%;}

        table {width: 100%;}
        tfoot tr th:first-child {text-align: left;}

        .centered {
            text-align: center;
            align-content: center;
        }

        small{font-size:11px;}

        @media print {
            * {
                font-size:12px;
                line-height: 20px;
            }
            td,th {padding: 5px 0;}
            .hidden-print {
                display: none !important;
            }
            @page { margin: 0; } body { margin: 0.5cm; margin-bottom:1.6cm; }
        }
    </style>
</head>
<body>

<div style="max-width:400px;margin:0 auto">

    <div class="hidden-print">
        <table>
            <tr>
                {{--<td><a href="" class="btn btn-info"><i class="fa fa-arrow-left"></i> {{ __('Back') }}</a> </td>--}}
                <td><button onclick="window.print();" class="btn btn-primary"><i class="dripicons-print"></i> {{ __('Print') }}</button></td>
            </tr>
        </table>
        <br>
    </div>

    <div id="receipt-data">
        <div class="centered">
                <img src="{{asset('assets/vector_files/Black.svg')}}" height="42" width="80%" style="margin:10px 0;">
                
        </div>
        <div class="lefted">
            
            <p style="text-align: left">{{ __('Invoice Number') }} : {{ $courier->tracking_id }} &nbsp;&nbsp;&nbsp;&nbsp;
                <br>
                {{ __('Order Date') }} : {{ date('d M,Y',strtotime($courier->created_at)) }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </p>
        </div>

            <p style="text-emphasis: left;">{{ __('Sender Name') }}: {{ !empty($courier->customer->name)?$courier->customer->name:"Not Available" }}<br>
                {{ __('Phone') }}: {{ (!empty($courier->customer->phone)) ? $courier->customer->phone:'Not Available' }}<br>
                {{ __('Zone') }}: {{ (!empty($courier->sender_area->name)) ? $courier->sender_area->name:'Not Available' }}<br>
                Pickup Hub: {{ (!empty($courier->branch->name)) ? $courier->branch->name:'Not Available' }};
            </p>
            
            <p style="text-emphasis: left;">{{ __('Receiver Name') }}: {{$courier->receiver_name}}<br>
                {{ __('Address') }}: {{$courier->receiver_address}}<br>
                {{ __('Phone') }}: {{ (!empty($courier->receiver_phone)) ? $courier->receiver_phone:'Not Available' }}
                @if(!empty($courier->note)) <br>{{ __('Note') }}: {{ (!empty($courier->note)) ? $courier->note:'Not Available' }} @endif<br>
                @php
                    if($courier->delivery_hub == "0" || empty($courier->delivery_hub)){
                        $delivery_hub = \App\Models\Branch::find($courier->branch_id)->name?\App\Models\Branch::find($courier->branch_id)->name:"Not Available";
                    }else{
                        $delivery_hub = \App\Models\Branch::find($courier->delivery_hub)->name?\App\Models\Branch::find($courier->delivery_hub)->name:"Not Available";
                    }
                @endphp
                Delivery Hub: {{ $delivery_hub }};
            </p>

            <p style="text-emphasis: left;">
                

                @if($courier->paid_by != 'merged_with_cod')                  
                    @if($courier->cash_on_delivery == '1')

                        @if($courier->paid_by == "receiver")
                            <!--{{ __('Cash on Delivery amount') }}: {{$courier->cash_on_delivery_amount}} Taka<br>

                            {{ __('Delivery charge') }}: {{ !empty($courier->pricing->price)?$courier->pricing->price.' Taka':'Not Available' }}(Paid By {{ $courier->paid_by }})<br>-->

                            {{ __('Total amount to be collected') }} : {{ $courier->cash_on_delivery_amount + (!empty($courier->pricing->price)?$courier->pricing->price:0) }} Taka

                        @elseif($courier->paid_by == "sender")

                            {{ __('Total amount to be collected') }} : {{ $courier->cash_on_delivery_amount }} Taka
                        @endif

                    @else
                        @if($courier->paid_by == "sender")
                           {{ __('Total amount to be collected') }} : O Taka
                        @else
                          {{ __('Total amount to be collected') }} : {{ (!empty($courier->pricing->price)?$courier->pricing->price.' Taka':'Not Available') }}
                        @endif
                    @endif
                @else
                    {{ __('Total amount to be collected') }}: {{$courier->cash_on_delivery_amount}} Taka<br>
                @endif
            </p>

        <div class="centered" style="margin:30px 0 50px">
            <?php
               echo '<img src="data:image/png;base64,' . DNS1D::getBarcodePNG($courier->tracking_id, 'C128',1,22) . '" alt="barcode"   />';
            ?><br>
            <small><strong>{{ __('Invoice Generated By : Holister BD') }}</strong></small>
        </div>
    </div>
</div>

<script type="text/javascript">
    function auto_print() {
        window.print()
    }
    setTimeout(auto_print, 1000);
</script>

</body>
</html>
