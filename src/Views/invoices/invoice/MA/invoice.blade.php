@php
    \Carbon\Carbon::setLocale('fr');
@endphp
    <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Invoice #{{$invoice->id}}</title>

    <style>
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            /* border: 1px solid #eee; */
            /* box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); */
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        /** RTL **/
        .invoice-box.rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .invoice-box.rtl table {
            text-align: right;
        }

        .invoice-box.rtl table tr td:nth-child(2) {
            text-align: left;
        }

        .footer-text{
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.2;
            text-align: center;
            position: absolute;
            width: 100%;
            bottom:5px;
            left: 0;
            right: 0;
        }
    </style>
</head>

<body>
<div class="invoice-box">
    <table cellpadding="0" cellspacing="0">
        <tr class="top">
            <td colspan="2">
                <table>
                    <tr>
                        <td class="title">
                            <img src="{{asset('domains/'. parse_url(env('APP_URL'))['host'] .'/logo-color.png')}}" style="width: 100%; max-width: 160px" />
                        </td>

                        <td>
                            Facture : <strong> #{{($invoice->id + env('INVOICE_NUMBER_START', 0))}}</strong> <br>
                            Date: {{$invoice->created_at->format('d-m-Y')}}<br />
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="information">
            <td colspan="2">
                <table>
                    <tr>
                        <td>
                            WISE GLOBAL SHIPPING <br>
                            387 Bd Mohammed V, 20290 <br>
                            Casablanca Maroc<br>
                            +212 5222-48985 <br>
                            387@awsel.ma <br>
                        </td>

                        <td>
                            {{$invoice->company->name}}<br />
                            {{$invoice->company->email}}<br />
                            {{$invoice->company->phone}}<br />
                            {{$invoice->company->address}}<br />
                            @isset($invoice->company->legal_details['ice'])
                                ICE : {{$invoice->company->legal_details['ice']}}<br />
                            @endisset
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="heading">
            <td colspan="2">
                <table>
                    <tr>
                        <td style="border: 0;" width="60%">Description</td>
                        <td style="border: 0;text-align: center;" width="20%">Quantité</td>
                        <td style="border: 0;text-align: right;" width="20%">Prix</td>
                    </tr>
                </table>
            </td>
        </tr>

        @foreach ($invoice->details as $detail)
            @if(!$detail->canceled_at)
            <tr class="item last">
                <td colspan="2">
                    <table>
                        <tr>
                            <td width="60%">
                                {{$detail->product_name}}
                            </td>
                            <td width="20%" style="text-align: center;">{{$detail->quantity}}</td>
                            <td width="20%" style="text-align: right;">
                                {{$detail->price}}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            @endif
        @endforeach

        <tr>
            <td width="50%"></td>

            <td>
                <table>
                    @if ($invoice->total_discount>0)
                        <tr>
                            <td width="50%" style="text-align: left;">
                                Total Discount
                            </td>
                            <td width="50%" style="text-align: right;">
                                <strong>
                                    {{$invoice->total_discount}} {{env('WHITE_LABEL_CURRENCY')}}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%" style="text-align: left;">
                                Total TTC
                            </td>
                            <td width="50%" style="text-align: right;">
                                <strong>
                                    {{$invoice->total}} {{env('WHITE_LABEL_CURRENCY')}}
                                </strong>
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td width="50%" style="text-align: left;">
                                Total TTC
                            </td>
                            <td width="50%" style="text-align: right;">
                                <strong>
                                    {{$invoice->total}} {{env('WHITE_LABEL_CURRENCY')}}
                                </strong>
                            </td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>


    <br><br><br>
    <br><br><br>
    <br><br><br>

    <div class="footer-text">
        R.C.: 540229 - I.C.E.: 00301617600008
    </div>
</div>
</body>
</html>
