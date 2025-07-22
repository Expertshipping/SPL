<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Invoice</title>

    <style>
        @page { margin: 0px; }
        body { margin: 0px; }

        /* @font-face {
            font-family: 'Open Sans';
            font-weight: 400;
            src: url('{{ asset('/') }}fonts/OpenSans-Regular.ttf');
        }

        @font-face {
            font-family: 'Open Sans';
            font-weight: 600;
            src: url('{{ asset('/') }}fonts/OpenSans-SemiBold.ttf');
        }

        @font-face {
            font-family: 'Open Sans';
            font-weight: 700;
            src: url('{{ asset('/') }}fonts/OpenSans-Bold.ttf');
        } */

        body {
            background: #fff none;
            font-size: 12px;
        }

        *{
            font-family: 'Open Sans', Helvetica, Arial, sans-serif;
            line-height: 1;
            font-size: 10px;
        }

        h2 {
            font-size: 28px;
            color: #ccc;
        }

        .container {
            width: 710px;
            margin: 0 auto;
            position: relative;
        }

        .invoice-head td {
            padding: 0 8px;
        }

        .table{
            border-collapse:collapse
        }

        .table th {
            vertical-align: top;
            font-weight: bold;
            line-height: 1;
            text-align: left;
            border: none;
            padding: 10px 5px;
            color: #373b44;
            font-size: 12px;
        }

        .table tr.row td {
            border-bottom: 1px solid #ddd;
        }

        .table td {
            line-height: 1;
            text-align: left;
            vertical-align: top;
            padding: 10px 5px;
            border: none;
        }

        .table .color1 td{
            background-color: #f9f9f9;
        }

        .table .color2 td {
            background-color: #ffffff;
        }

        .table .no-padding td {
            padding: 0;
        }

        .text-right{
            text-align: right !important;
        }

        .text-center{
            text-align: center !important;
        }
        .bg-bordred{
            max-width: 100%;
            background-color: #f9f9f9;
            padding: 2px;
            border: 1px solid #ddd;
        }
        .class1{

            left: 0;
            right: 0;
            top: 0;
        }

        /*  */

        .section1{
            margin: 30px 0;
            height: 80px;
        }

        .section1 .invoice-number{
            color: #1F3144;
            text-align: center;
            font-family: 'Open Sans', Helvetica, Arial, sans-serif;
            font-size: 14px;
            font-weight: 700;
            line-height: 14px;
            text-transform: uppercase;
            border-radius: 3px;
            border: 1px solid #1F3144;
            background: rgba(255, 255, 255, 0.20);
            padding: 5px 30px;
        }

        .section2{
            padding: 20px;
            border-radius: 6px;
            background: #F9F9F9;
            min-height: 90px;
        }

        .section3{
            margin-top: 20px;
        }

        .section3 table{
            margin: 0 auto;
            border-collapse: collapse;
            padding: 0;
        }

        .section3 table th{
            padding: 13px 10px;
            border-right: 1px solid #FFF;
            background: #1F3144;
            color: #FFF;
            font-family: 'Open Sans', Helvetica, Arial, sans-serif;
            font-size: 11px;
            font-weight: 600;
            line-height: 11px; /* 154.545% */
            border: 0.5px solid #FFF;
        }

        .section3 table td{
            padding: 13px 10px;
            border-right: 1px solid #FFF;
            background: #FFFFFF;
            color: #1F3144;
            font-family: 'Open Sans', Helvetica, Arial, sans-serif;
            font-size: 11px;
            font-weight: 400;
            line-height: 11px; /* 154.545% */
            border: 0.5px solid #1F3144;
        }

        .section4 {
            min-height: 100px;
            width: 100%;
            margin-top: 20px;
        }

        .section4 .section4-child{
            border: 0.5px solid #1F3144;
            background: #F9F9FA;
            width: 300px;
            float: right;
            padding: 0 15px;
            min-height: 60px;
        }

        .section4 .section4-line{
            min-height: 40px;
        }

        .section4 .section4-line>div{
            padding: 10px 0;
            width: 50%;
        }

        .section4 .section4-line>div.text{
            color: #1F3144;
            font-family: 'Open Sans', Helvetica, Arial, sans-serif;
            font-size: 12px;
            font-weight: 400;
            line-height: 100%;
        }

        .section4 .section4-line>div.value{
            color: #1F3144;
            text-align: right;
            font-family: 'Open Sans', Helvetica, Arial, sans-serif;
            font-size: 13px;
            font-weight: 600;
            line-height: 100%;
        }

        .text-right{
            text-align: right;
        }

        .text-small{
            color: #1F3144;
            font-family: 'Open Sans', Helvetica, Arial, sans-serif;
            font-size: 10px;
            font-weight: 400;
            line-height: normal;
            margin: 10px 0;
        }

        .float-right{
            float: right;
        }

        .float-left{
            float: left;
        }

        .total{
            border: 0.5px solid #1F3144;
            background: #F9F9FA;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            left: 0;
            right: 0;
            text-align: center;
            border-top: 1px solid #DFE4EA;
            background: #F9F9FA;
            padding: 10px 30px 30px 30px
        }

        .footer table{
            width: 100%;
            border-collapse: collapse;
            text-align:center;
        }

        .footer table td{
            text-align: center;
        }

        .signature{
            width: 100%;
            text-align: right;
        }

        .signature img{
            width: 240px;
            margin: 0 60px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="section1">
        <div class="float-left">
            <img src="{{ asset('domains/'. request()->getHost() .'/logo-color.png') }}" alt="" width="120px">
        </div>

        <div class="text-right float-right">
            <div class="invoice-number">
                {{__('Invoice')}}# {{$invoice->id}}
            </div>

            <div class="text-small">
                {{__('Date')}} : {{$invoice->created_at->format('d / m / Y')}}
            </div>
        </div>
    </div>

    <div class="section2">
        <div class="float-left" style="font-size: 13px;margin: 0;text-transform:uppercase;">
            <span>
                @if(env('APP_NAME', 'ShipPayLess') === 'Awsel')
                    <strong>WISE GLOBAL SERVICES</strong> <br>
                    387 Bd Mohammed V, Casablanca <br>
                    20290 Casablanca MA <br>
                    +212 5222-48985 <br>
                    contact@awsel.ma
                @endif

                @if(env('APP_NAME', 'ShipPayLess') === 'Expert Shipping')
                    <strong>{{ env('APP_NAME') }}</strong> <br>
                    7005 Bd Taschereau <br>
                    Brossard, QC J4Z 3P5, Canada <br>
                    +1 456 636 636 <br>
                    contact@expertshipping.ca
                @endif

                @if(env('APP_NAME', 'ShipPayLess') === 'ShipPayLess')
                    <strong>{{ env('APP_NAME') }}</strong> <br>
                    7005 Bd Taschereau <br>
                    Brossard, QC J4Z 3P5, Canada <br>
                    +1 456 636 636 <br>
                    contact@shippayless.com
                @endif
            </span>
        </div>

        <div class="float-right" style="font-size: 13px;margin: 0;text-transform:uppercase; text-align:right;">
            @if (isset($chargedUser))
                <strong>{{ $chargedUser->company->name }}</strong> <br>
                <span>
                    {{ $chargedUser->company->addr1 }}<br>
                    @if ($chargedUser->company->addr2)
                        {{ $chargedUser->company->addr2 }} <br>
                    @endif
                    {{ $chargedUser->company->city }},
                    {{ $chargedUser->company->state }},
                    {{ $chargedUser->company->zip_code }},
                    {{ $chargedUser->company->country }}<br>
                    @if (isset($chargedUser->company->legal_details['ice']))
                        ICE:
                        {{ $chargedUser->company->legal_details['ice'] }}<br> <br>
                    @endif
                    @if (isset($vat))
                        <br>
                        {{ $vat }}<br> <br>
                    @endif

                    @if (isset($url))
                        <a href="{{ $url }}">{{ $url }}</a>
                    @endif
                </span>
            @endif
        </div>
    </div>

    {{-- <div style="text-align: right;margin-right:5px;right: 0;top: 90px;">
        <span style="font-size:6x;">{{__('Amounts expressed in')}} {{request()->platformCountry?->currency}}</span>
    </div>

    <div style="width: 200px;display: block;background: #dddddd82;top: 20px;right: 0px;padding: 24px;font-size: 17px;margin: 0;font-weight:bold;text-transform: uppercase;box-shadow: -3px 3px 3px -1px #00000042;">
        {{__('Total')}} : {{ $invoice->total }}
    </div> --}}

    {{-- <div style="position: relative;height: 60px;margin-bottom: 80px;">
        <p style="left: 44px;top: 40px;font-size: 10px;margin: 0;">
            @include('invoices.payment-details', ['invoice' => $invoice, 'chargedUser' => $chargedUser])
        </p>
    </div> --}}

    <div class="section3">
        <table width="100%" border="0">
            <tr>
                <th align="left">{{__('Item description')}}</th>
                <th align="left">{{__('Quantity')}}</th>
                <th align="left">{{__('Price')}}</th>
                <th align="left">{{__('Total')}}</th>
            </tr>
            @foreach ($invoice->details->whereNull('canceled_at') as $detail)
                <tr class="color1">
                    <td width="40%">
                        @if ($detail->invoiceable)
                            @if (get_class($detail->invoiceable) === App\Shipment::class)
                                @include('spl::invoices.shipment-details', ['shipment' => $detail->invoiceable ])
                            @endif
                            @if (get_class($detail->invoiceable) === App\Insurance::class)
                                @include('spl::invoices.insurance-details', ['insurance' => $detail->invoiceable ])
                            @endif
                            @if (get_class($detail->invoiceable) === App\ShipmentSurcharge::class)
                                @include('spl::invoices.shipment-surcharge-details', ['shipmentSurcharge' => $detail->invoiceable ])
                            @endif
                            @if (get_class($detail->invoiceable) === App\Product::class)
                                @php
                                    $detail->invoiceable->loadMissing('category');
                                @endphp
                                {{$detail->invoiceable->category->name}}:
                                <strong>{{$detail->invoiceable->name}}</strong>
                            @endif
                            @if (get_class($detail->invoiceable)=== App\ShipmentSurcharge::class)
                                {{$detail->invoiceable->name}}:
                                <strong>{{$detail->invoiceable->description}}</strong>
                            @endif
                        @endif
                    </td>
                    <td>
                        {{ $detail->quantity }}
                    </td>
                    <td>
                        {{ $detail->price }} {{ request()->platformCountry?->currency }}
                    </td>
                    <td>
                        <strong>
                            {{ ($detail->price*$detail->quantity) }} {{ request()->platformCountry?->currency }}
                        </strong>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="section4">
        <div class="section4-child">
            <div class="section4-line">
                <div class="text float-left">
                    {{__('Subtotal')}} :
                </div>
                <div class="value float-right">
                    {{ isset($invoice->taxes['taxes']) ? $invoice->taxes['preTax'] : $invoice->total }}  {{ request()->platformCountry?->currency }}
                </div>
            </div>

            @if (isset($invoice->taxes['taxes']))
                @foreach ($invoice->taxes['taxes'] as $key => $tax)
                    <div class="section4-line">
                        <div class="text float-left">
                            {{ $key }} :
                        </div>
                        <div class="value float-right">
                            {{ $tax }} {{ request()->platformCountry?->currency }}
                        </div>
                    </div>
                @endforeach
            @endif

            <div class="section4-line">
                <div class="text float-left">
                    {{__('Total')}} :
                </div>
                <div class="value float-right">
                    {{ $invoice->total }} {{ request()->platformCountry?->currency }}
                </div>
            </div>
        </div>
    </div>

    @if(env('APP_NAME', 'ShipPayLess') === 'Awsel')
        <div class="signature">
            <img src="{{ asset('domains/'. request()->getHost() .'/invoice-signature.png') }}" />
        </div>
    @endif

    <div class="footer">
        <table width="100%">
            @if(env('APP_NAME', 'ShipPayLess') === 'Awsel')
                <tr>
                    <td width="33%">
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 700; line-height: 12px; word-wrap: break-word">{{ env('APP_NAME') }}<br/>Adresse:</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 400; line-height: 12px; word-wrap: break-word"> 387 Bd Mohammed V, <br/>Casablanca 20290 Maroc</span>
                    </td>

                    <td width="33%">
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 700; line-height: 12px; word-wrap: break-word">PATENTE:</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 400; line-height: 12px; word-wrap: break-word">36366227</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 700; line-height: 12px; word-wrap: break-word">CNSS:</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 400; line-height: 12px; word-wrap: break-word">4127948 <br/></span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 700; line-height: 12px; word-wrap: break-word">RC:</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 400; line-height: 12px; word-wrap: break-word">540229</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 700; line-height: 12px; word-wrap: break-word">IF:</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 400; line-height: 12px; word-wrap: break-word">52418234<br/></span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 700; line-height: 12px; word-wrap: break-word">ICE:</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 400; line-height: 12px; word-wrap: break-word">003016176000083</span>
                    </td>

                    <td width="33%">
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 700; line-height: 12px; word-wrap: break-word">E-mail:</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 400; line-height: 12px; word-wrap: break-word"> contact@awsel.ma<br/></span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 700; line-height: 12px; letter-spacing: 0.27px; word-wrap: break-word">Site web:</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 400; line-height: 12px; letter-spacing: 0.27px; word-wrap: break-word"> www.awsel.ma <br/></span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 700; line-height: 12px; letter-spacing: 0.27px; word-wrap: break-word">Tél:</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 400; line-height: 12px; letter-spacing: 0.27px; word-wrap: break-word"> +212 (5) 22 24 89 85</span>
                    </td>
                </tr>
            @endif

            @if(env('APP_NAME', 'ShipPayLess') === 'ShipPayLess')
                <tr>
                    <td width="33%">
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 700; line-height: 12px; word-wrap: break-word">{{ env('APP_NAME') }}<br/>Adresse:</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 400; line-height: 12px; word-wrap: break-word"> 7005 Bd Taschereau <br> Brossard, QC J4Z 3P5, Canada</span>
                    </td>

                    <td width="33%">

                    </td>

                    <td width="33%">
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 700; line-height: 12px; word-wrap: break-word">E-mail:</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 400; line-height: 12px; word-wrap: break-word"> contact@shippayless.com<br/></span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 700; line-height: 12px; letter-spacing: 0.27px; word-wrap: break-word">Site web:</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 400; line-height: 12px; letter-spacing: 0.27px; word-wrap: break-word"> www.shippayless.com <br/></span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 700; line-height: 12px; letter-spacing: 0.27px; word-wrap: break-word">Tél:</span>
                        <span style="color: #1F3144; font-size: 9px; font-family: 'Open Sans', Helvetica, Arial, sans-serif; font-weight: 400; line-height: 12px; letter-spacing: 0.27px; word-wrap: break-word"> +1 456 636 636</span>
                    </td>
                </tr>
            @endif
        </table>
    </div>
</div>
</body>
</html>
