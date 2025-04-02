@php
    \Carbon\Carbon::setLocale('fr');
@endphp
    <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Invoice #{{$invoice->id}}</title>

    <style>
        body, html {
            padding: 0;
            margin: 0;
        }
        html{
            margin: 5px;
        }
        .invoice-box {
            max-width: 900px;
            margin: auto;
            padding: 0px;
            /* border: 1px solid #eee; */
            /* box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); */
            font-size: 14px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table.padding td {
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
            padding: 0;
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

        .totaux{
            width: 100%;
        }

        .totaux tr td{
            padding: 5px;
        }

        .totaux tr td:nth-child(2){
            text-align: right;
        }
    </style>
</head>

<body>
<div class="invoice-box">
    <table cellpadding="0" cellspacing="0" class="padding">
        <tr class="top">
            <td colspan="2">
                <table>
                    <tr>
                        <td class="title">
                            <img src="{{asset('domains/'. parse_url(env('APP_URL'))['host'] .'/logo-color.png')}}" style="width: 100%; max-width: 160px" />
                        </td>

                        <td>
                            Facture : <strong> #{{ $invoice->invoice_number }}</strong> <br>
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
                            WISE GLOBAL SERVICES <br>
                            387 Bd Mohammed V, 20290 <br>
                            Casablanca Maroc<br>
                            +212 5222-48985 <br>
                            contact@awsel.ma <br>
                        </td>

                        <td>
                            @if($invoice->company->name)
                                {{$invoice->company->name}}<br />
                            @endif
                            @if($invoice->company->email)
                                {{$invoice->company->email}}<br />
                            @endif
                            @if($invoice->company->phone)
                                {{$invoice->company->phone}}<br />
                            @endif
                            @if($invoice->company->addr1)
                                {{$invoice->company->addr1}}<br />
                            @endif
                            @isset($invoice->company->legal_details['ice'])
                                ICE : {{$invoice->company->legal_details['ice']}}<br />
                            @endisset
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0" >
        {{--       TABLE HEADER         --}}
        <tr class="heading">
            <td colspan="2">
                <table class="padding">
                    <tr>
                        <td style="border: 0;" width="40%">Description</td>
                        <td style="border: 0;text-align: center;" width="15%">Quantité</td>
                        <td style="border: 0;text-align: right;" width="15%">Prix HT</td>
                        <td style="border: 0;text-align: right;" width="15%">TVA</td>
                        <td style="border: 0;text-align: right;" width="15%">Prix TTC</td>
                    </tr>
                </table>
            </td>
        </tr>

        {{--       DETAILS         --}}
        @foreach ($invoice->details as $key => $detail)
            @if(!$detail->canceled_at)
                <tr class="item">
                    <td colspan="2">
                        <table class="padding">
                            <tr>
                                <td width="40%">
                                    @if ($detail->invoiceable_type==="App\Refund")
                                        Refund
                                    @endif

                                    @if ($detail->invoiceable_type==="App\Shipment")
                                        @php
                                            $shipment = App\Shipment::whereId($detail->invoiceable_id)->withService()->first();
                                        @endphp
                                        {{ $shipment->carrier->name }}
                                        ({{ $shipment->service?->name }}) : <br>
                                        {{ $shipment->tracking_number }} <br>
                                        @if ($shipment->pickup && $shipment->pickup->pickup_number)
                                            Pickup Confirmation : {{$shipment->pickup->pickup_number}}
                                        @endif
                                    @endif

                                    @if ($detail->invoiceable_type==="App\Product")
                                        {{ $detail->invoiceable->name }}
                                        @if(isset($detail->meta_data['tracking_number']))
                                            : {{ $detail->meta_data['tracking_number'] }}
                                        @endif
                                    @endif

                                    @if ($detail->invoiceable_type==="App\Insurance")
                                        {{ __("Insurance Number : ") }} {{ $detail->invoiceable->transaction_number }} <br>
                                        {{ __("Insured Value : ") }} {{ env('WHITE_LABEL_CURRENCY', '$') }} {{ $detail->invoiceable->declared_value }}
                                    @endif

                                    @if ($detail->invoiceable_type==="App\Insurance")
                                        {{ __("Insurance Number : ") }} {{ $detail->invoiceable->transaction_number }} <br>
                                        {{ __("Insured Value : ") }} {{ env('WHITE_LABEL_CURRENCY', '$') }} {{ $detail->invoiceable->declared_value }}
                                    @endif

                                    @if ($detail->invoiceable_type==="App\AdditionalService")
                                        {{ $detail->invoiceable->name }}
                                    @endif
                                </td>
                                <td width="15%" style="text-align: center;">{{$detail->quantity}}</td>
                                <td width="15%" style="text-align: right;">
                                    {{\ExpertShipping\Spl\Helpers\Helper::moneyFormat($detail->total_ht/$detail->quantity, env('WHITE_LABEL_CURRENCY', 'CAD'))}}
                                </td>
                                <td width="15%" style="text-align: right;">
                                    {{\ExpertShipping\Spl\Helpers\Helper::moneyFormat($detail->total_taxes/$detail->quantity, env('WHITE_LABEL_CURRENCY', 'CAD'))}}
                                </td>
                                <td width="15%" style="text-align: right;">
                                    {{\ExpertShipping\Spl\Helpers\Helper::moneyFormat($detail->total_ht + $detail->total_taxes, env('WHITE_LABEL_CURRENCY', 'CAD'))}}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            @endif
        @endforeach

    </table>

    <br><br>

    {{--        TOTAL        --}}
    <table cellpadding="0" cellspacing="0" class="padding">
        <tr>
            <td width="50%"></td>
            <td>
                <table class="totaux">
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
                                Total HT
                            </td>
                            <td width="50%" style="text-align: right;">
                                <strong>
                                    {{ \ExpertShipping\Spl\Helpers\Helper::moneyFormat($invoice->total_ht, env('WHITE_LABEL_CURRENCY', 'CAD')) }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%" style="text-align: left;">
                                Total TVA
                            </td>
                            <td width="50%" style="text-align: right;">
                                <strong>
                                    {{ \ExpertShipping\Spl\Helpers\Helper::moneyFormat($invoice->total_taxes, env('WHITE_LABEL_CURRENCY', 'CAD')) }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%" style="text-align: left;">
                                Total TTC
                            </td>
                            <td width="50%" style="text-align: right;">
                                <strong>
                                    {{ \ExpertShipping\Spl\Helpers\Helper::moneyFormat($invoice->total_ht + $invoice->total_taxes, env('WHITE_LABEL_CURRENCY', 'CAD')) }}
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
