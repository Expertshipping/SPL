<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Invoice</title>

    <style>
        @page { margin: 0px; }
        body { margin: 0px; }
        /*@font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: normal;
            src: url(http://themes.googleusercontent.com/static/fonts/opensans/v8/cJZKeOuBrn4kERxqtaUH3aCWcynf_cDxXwCLxiixG1c.ttf) format('truetype');
        }*/
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
            /* padding-top: 30px; */
            width: 794px;
            height: 1120px;
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
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="" style="position: relative; height:120px;margin-bottom: 50px;">
        <img src="{{ asset('assets/invoices/invoice-header.png') }}" alt="test" class="class1" width="794px" height="120px">
        <h1 style="position: absolute;
        margin: 0;
        color: #fff;
        font-size: 30px;
        left: 136px;
        top: 25px;
        ">
            {{-- {{__('INVOICE')}} --}}
            <img src="{{ asset('domains/'. request()->getHost() .'/logo-white.png') }}" alt="" width="100px">
        </h1>

        <p style="position: absolute;margin: 0;color: #fff;font-size: 12px;left: 200px;top: 95px;">
            {{__('Invoice')}}# {{$invoice->id}}
        </p>

        <p style="position: absolute;margin: 0;color: #fff;font-size: 12px;left: 638px;top: 97px;">
            {{__('Date')}} : {{$invoice->created_at->format('d / m / Y')}}
        </p>
    </div>

    <div style="position: relative;height: 120px;margin-bottom: 0px;">
        <p style="position: absolute;left: 44px;top: 40px;font-size: 13px;margin: 0;font-weight: bold;text-transform:uppercase;">
            {{__('Invoice to')}} :
        </p>
        <p style="position: absolute;left: 160px;top: 40px;font-size: 13px;margin: 0;font-weight: bold;">
            @if (isset($chargedUser))
                {{ $chargedUser->company->name }}
            @endif
        </p>

        <p style="position: absolute;left: 160px;top: 60px;margin: 0;">
            @if (isset($chargedUser))
                {{ $chargedUser->company->addr1 }}<br>
                @if ($chargedUser->company->addr2)
                    {{ $chargedUser->company->addr2 }} <br>
                @endif
                {{ $chargedUser->company->city }},
                {{ $chargedUser->company->state }},
                {{ $chargedUser->company->zip_code }},
                {{ $chargedUser->company->country }}<br>
            @endif
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
        </p>
        <div style="text-align: right;margin-right:5px;position: absolute;right: 0;top: 90px;">
            <span style="font-size:6x;">{{__('Amounts expressed in')}} {{request()->platformCountry?->currency}}</span>
        </div>

        <div style="width: 200px;display: block;position: absolute;background: #dddddd82;top: 20px;right: 0px;padding: 24px;font-size: 17px;margin: 0;font-weight:bold;text-transform: uppercase;box-shadow: -3px 3px 3px -1px #00000042;">
            {{__('Total')}} : {{ $invoice->total }}
        </div>
    </div>

    <div style="position: relative;height: 60px;margin-bottom: 80px;">
        <p style="position: absolute;left: 44px;top: 40px;font-size: 10px;margin: 0;">
            @include('invoices.payment-details', ['invoice' => $invoice, 'chargedUser' => $chargedUser])
        </p>
    </div>

    @if ($invoice->details->count()===0)
        <div style="width:794px;">
            <table width="100%" style="margin: 0 auto;" class="table" border="0">
                <tr>
                    <th width="5%"></th>
                    <th align="left">{{__('Item description')}}</th>
                    <th align="left">{{__('Price')}}</th>
                    <th align="left">{{__('Total')}}</th>
                    <th width="5%"></th>
                </tr>
                <tr class="color1">
                    <td width="5%"></td>
                    <td width="40%">
                        @if ($invoice->invoiceable_type && $invoice->invoiceable)
                            @if (get_class($invoice->invoiceable)=== App\Shipment::class)
                                @include('invoices.shipment-details', ['shipment' => $invoice->invoiceable])
                            @endif
                            @if (get_class($invoice->invoiceable)=== App\Insurance::class)
                                @include('invoices.insurance-details', ['insurance' => $invoice->invoiceable])
                            @endif
                        @else
                            @include('invoices.surchages-details')
                        @endif
                    </td>
                    <td>
                        {{ $invoice->total }}
                    </td>
                    <td>
                        <table border="0" class="table" width="100%">
                            @if(isset($invoice->taxes['taxes']))
                                <tr class="no-padding">
                                    <td width="50%"><span>{{__('Sub total')}}</span></td>
                                    <td class="text-right"><span>{{$invoice->taxes['preTax']}}</span></td>
                                </tr>

                                @foreach ($invoice->taxes['taxes'] as $key => $tax)
                                    <tr class="no-padding">
                                        <td><span>{{__($key)}}</span></td>
                                        <td class="text-right">{{$tax}}</td>
                                    </tr>
                                @endforeach

                                <tr class="no-padding">
                                    <td></td>
                                    <td class="text-right">
                                        <hr style="border: 0;border-bottom:1px solid #ddd;">
                                        <strong style="">
                                            Total: {{$invoice->total}}
                                        </strong>
                                    </td>
                                </tr>
                            @else
                                <tr class="no-padding">
                                    <td></td>
                                    <td class="text-right">
                                        <strong style="">
                                            {{$invoice->total}}
                                        </strong>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </td>
                    <td width="5%"></td>
                </tr>
                <tr class="no-padding">
                    <td width="5%"></td>
                    <td colspan="3">
                        <hr style="border: 0;border-bottom:2px solid #ddd;margin: 0 auto;width: 100%;">
                    </td>
                    <td width="5%"></td>
                </tr>
                <tr class="color2">
                    <td width="5%"></td>
                    <td colspan="3">
                        <br>
                    </td>
                    <td width="5%"></td>
                </tr>
            </table>
        </div>
    @else
        <div style="width:794px;">
            <table width="100%" style="margin: 0 auto;" class="table" border="0">
                <tr>
                    <th width="5%"></th>
                    <th align="left">{{__('Item description')}}</th>
                    <th align="left">{{__('Quantity')}}</th>
                    <th align="left">{{__('Price')}}</th>
                    <th align="left">{{__('Total')}}</th>
                    <th width="5%"></th>
                </tr>
                @foreach ($invoice->details as $detail)
                    <tr class="color1">
                        <td width="5%"></td>
                        <td width="40%">
                            @if ($detail->invoiceable)
                                @if (get_class($detail->invoiceable)=== App\Shipment::class)
                                    @include('invoices.shipment-details', ['shipment' => $detail->invoiceable ])
                                @endif
                                @if (get_class($detail->invoiceable)=== App\Insurance::class)
                                    @include('invoices.insurance-details', ['insurance' => $detail->invoiceable ])
                                @endif
                                @if (get_class($detail->invoiceable)=== App\Product::class)
                                    @php
                                        $detail->invoiceable->loadMissing('category');
                                    @endphp
                                    {{$detail->invoiceable->category->name}}:
                                    <strong>{{$detail->invoiceable->name}}</strong>
                                @endif
                            @endif
                        </td>
                        <td>
                            {{ $detail->quantity }}
                        </td>
                        <td>
                            {{ $detail->price }}
                        </td>
                        <td>
                            <strong>
                                {{ ($detail->price*$detail->quantity) }}
                            </strong>
                        </td>
                        <td width="5%"></td>
                    </tr>
                @endforeach
                <tr class="no-padding">
                    <td width="5%"></td>
                    <td colspan="4">
                        <hr style="border: 0;border-bottom:2px solid #ddd;margin: 0 auto;width: 100%;">
                    </td>
                    <td width="5%"></td>
                </tr>
                <tr class="color2">
                    <td width="5%"></td>
                    <td colspan="4">
                        <br>
                    </td>
                    <td width="5%"></td>
                </tr>
            </table>
        </div>
    @endif

    <div style="width:794px;margin-bottom:20px">
        <table width="100%" style="margin: 0 auto;" class="table" border="0">
            <tr>
                <td width="5%"></td>
                <td width="40%">
                    <strong>
                        {{__('We appreciate your business. Thank you!')}}
                    </strong>
                    <h3 style="font-size: 10px;margin:5px 0;color:#009ed3">
                        {{__('General Inquiries')}}
                    </h3>
                    <p style="font-size:8px;">
                        {{
                            (request()->platformCountry?->code ?? 'CA')==="CA" ? 'accounting@expertshipping.ca':'contact@awsel.ma'
                        }}
                    </p>
                </td>
                <td width="10%"></td>
                <td width="40%">
                    <table border="0" class="table" width="100%">
                        @isset($invoice->taxes['taxes'])
                            <tr class="no-padding">
                                <td>{{__('Sub total')}}</td>
                                <td class="text-right" style="font-weight: bold;">
                                    {{$invoice->taxes['preTax']}}
                                </td>
                            </tr>
                            {{-- <tr class="no-padding">
                                <td>{{__('Tax')}}</td>
                                <td class="text-right" style="font-weight: bold;">
                                    @isset($invoice->taxes['preTax'])
                                        {{$invoice->total - $invoice->taxes['preTax']}}
                                    @endisset
                                </td>
                            </tr> --}}
                            @foreach ($invoice->taxes['taxes'] as $key => $tax)
                                <tr class="no-padding">
                                    <td>
                                        <span>{{__($key)}} </span>
                                        @if($key==="QST")
                                            1227168842TQ0001
                                        @else
                                            752183079RT0001
                                        @endif
                                    </td>
                                    <td class="text-right">{{$tax}}</td>
                                </tr>
                            @endforeach
                            <tr class="no-padding">
                                <td colspan="2">
                                    <hr style="border: 0;border-bottom:1px solid #ddd;">
                                </td>
                            </tr>
                        @endisset
                        <tr class="no-padding">
                            <td>{{__('Total')}}</td>
                            <td class="text-right" style="font-weight: bold;">
                                {{$invoice->total}}
                            </td>
                        </tr>
                        <tr class="no-padding">
                            <td colspan="2">
                                <br><br><br><br><br>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="5%"></td>
            </tr>
        </table>

        @if((request()->platformCountry?->code ?? 'CA') === 'MA')
        <table width="100%" style="margin: 0 auto;" class="table" border="0">
            <tr>
                <td style="text-align: center">
                    WISE GLOBAL SERVICES SARL - R.C.: 540229 - I.C.E.: 00301617600008
                </td>
            </tr>
        </table>
        @endif
    </div>

    <div class="" style="position: fixed; height:50px;bottom: 0;">
        <img src="{{ asset('assets/invoices/invoice-footer.png') }}" alt="test" class="class1" width="794px" height="50px">
    </div>
</div>
</body>
</html>
