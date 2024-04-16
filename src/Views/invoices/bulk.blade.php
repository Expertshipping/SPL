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

        @if ($invoices->count()<=10)
            <p style="position: absolute;margin: 0;color: #fff;font-size: 12px;left: 200px;top: 95px;">
                {{__('Invoices')}}: {{$invoices->implode('id', ', ')}}
            </p>
        {{-- @else
            <p style="position: absolute;margin: 0;color: #fff;font-size: 12px;left: 200px;top: 95px;">
                {{__('Invoices')}}: {{$invoices->first()->id}}... {{$invoices->last()->id}}
            </p> --}}
        @endif

        <p style="position: absolute;margin: 0;color: #fff;font-size: 12px;left: 638px;top: 97px;">
            {{__('Date')}} : {{now()->format('d / m / Y')}}
        </p>
    </div>

    <div style="position: relative;height: 120px;margin-bottom: 0px;">
        <p style="position: absolute;left: 44px;top: 40px;font-size: 13px;margin: 0;font-weight: bold;text-transform:uppercase;">
            {{__('Invoice to :')}}
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
            <span style="font-size:6x;">{{__('Amounts expressed in')}} {{env('WHITE_LABEL_CURRENCY')}}</span>
        </div>

        <div style="width: 200px;display: block;position: absolute;background: #dddddd82;top: 20px;right: 0px;padding: 24px;font-size: 17px;margin: 0;font-weight:bold;text-transform: uppercase;box-shadow: -3px 3px 3px -1px #00000042;">
            {{__('Total')}} : {{ $invoices->sum('total') }}
        </div>
    </div>

    <div style="position: relative;height: 60px;margin-bottom: 80px;">
        <p style="position: absolute;left: 44px;top: 40px;font-size: 10px;margin: 0;">
            @if(isset($bulkInvoice))
                @include('invoices.payment-details', ['invoice' => $bulkInvoice, 'chargedUser' => $chargedUser])
            @endif
        </p>
    </div>

    <div style="width:794px;min-height:440px;margin-bottom:{{$invoices->count()===3?'30px':'60px'}}">
        <table width="100%" style="margin: 0 auto;" class="table" border="0">
            <tr>
                <th width="5%"></th>
                <th align="left">{{__('Item description')}}</th>
                <th align="left">{{__('Price')}}</th>
                <th align="left">{{__('Total')}}</th>
                <th width="5%"></th>
            </tr>
            @foreach ($invoices as $key => $invoice)
                <tr class="{{$key % 2 == 0?'color1':'color2'}}">
                    <td width="5%"></td>
                    <td width="40%">
                        @if(!isset($bulkInvoice))
                            @include('invoices.payment-details', ['invoice' => $invoice, 'chargedUser' => $chargedUser])
                        @endif
                        <br>
                        {{ __('Invoice Number') }} : <strong>{{$invoice->id}}</strong> <br>
                        @if ($invoice->invoiceable)
                            @if (get_class($invoice->invoiceable)=== App\Shipment::class)
                                @include('invoices.shipment-details', ['shipment' => $invoice->invoiceable ])
                            @endif

                            @if (get_class($invoice->invoiceable)=== App\Insurance::class)
                                @include('invoices.insurance-details', ['insurance' => $invoice->invoiceable ])
                            @endif
                        @endif
                    </td>
                    <td>
                        {{ $invoice->total }}
                    </td>
                    <td valign="bottom">
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
                                            {{ __('Total') }}: {{$invoice->total}}
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
            @endforeach
        </table>
    </div>

    <div style="width:794px;height:150px;">

    </div>

    <div style="width:794px;height:150px;margin-bottom:20px;position: fixed;bottom: 70px;">
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
                            env('WHITE_LABEL_COUNTRY', 'CA')==="CA" ? 'accounting@expertshipping.ca':'contact@awsel.ma'
                        }}
                    </p>
                    <hr style="border: 0;border-bottom:1px solid #ddd;">
                </td>
                <td width="10%"></td>
                <td width="40%">
                    <table border="0" class="table" width="100%">
                        <tr class="no-padding">
                            <td>{{__('Sub total')}}</td>
                            <td class="text-right" style="font-weight: bold;">
                                {{$invoices->sum('taxes.preTax')}}
                            </td>
                        </tr>
                        {{-- <tr class="no-padding">
                            <td>{{__('Tax')}}</td>
                            <td class="text-right" style="font-weight: bold;">
                                {{$invoices->sum('total') - $invoices->sum('taxes.preTax')}}
                            </td>
                        </tr> --}}

                        @if($invoices->sum('taxes.taxes.GST')>0)
                        <tr class="no-padding">
                            <td><span>{{__("GST")}} 752183079RT0001</span></td>
                            <td class="text-right">{{$invoices->sum('taxes.taxes.GST')}}</td>
                        </tr>
                        @endif

                        @if($invoices->sum('taxes.taxes.QST')>0)
                        <tr class="no-padding">
                            <td><span>{{__("QST")}} 1227168842TQ0001</span></td>
                            <td class="text-right">{{$invoices->sum('taxes.taxes.QST')}}</td>
                        </tr>
                        @endif

                        <tr class="no-padding">
                            <td colspan="2">
                                <hr style="border: 0;border-bottom:1px solid #ddd;">
                            </td>
                        </tr>
                        <tr class="no-padding">
                            <td>{{__('Total')}}</td>
                            <td class="text-right" style="font-weight: bold;">
                                {{$invoices->sum('total')}}
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

        @if(env('WHITE_LABEL_COUNTRY', 'CA') === 'MA')
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
