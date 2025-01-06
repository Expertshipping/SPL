@php
    App::setLocale($lang??$invoice->company->local);
@endphp
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Reçu de {{env('APP_NAME')}}</title>
        <style>
            html, body {
                background-color: #f2f4f5;
            }
            * {
                font-size: 12px;
                font-family: sans-serif;
                line-height: 1.3;
                color: #3d454d;
            }
            td,
            th,
            tr,
            table {
                border-top: 1px dashed #e0e1e2;
                border-collapse: collapse;
                padding: 5px 0;
            }

            .centered {
                text-align: center;
                align-content: center;
            }

            .ticket {
                width: 450px;
                max-width: 100%;
                text-align: center;
                margin: 0 auto;
                background: #fff;
            }

            img {
                max-width: inherit;
                width: inherit;
            }

            @media print {
                .hidden-print,
                .hidden-print * {
                    display: none !important;
                }
            }

            .small{
                font-size: 8px;
                line-height: 8px;
                text-align: center;
                display: block;
            }

            .img{
                width: 50mm;
                max-width: 50mm;
            }
            h1{
                font-size: 6mm;
            }

            tabl.no-border, table.no-border tr, table.no-border td{
                border-top: 0 !important;
            }

            .no-border{
                border-top: 0 !important;
            }

            .total-price{
                font-size: 20px;
                font-weight: bold;
            }

            .header{
                background-image: url("{{asset('assets/invoices/spacer.png')}}");
                background-color: #48799d;
                padding: 50px 0;
                background-repeat: no-repeat;
                background-size: 100%;
            }

            .store-info{
                background: #2bc4f4;
                padding: 5px 0;
            }

            .store-info *{
                color: #fff !important;
            }

            .text-uppercase{
                text-transform: uppercase;
            }

            .text-left{
                text-align: left !important;
            }

            .total{
                font-size: 55px;
                font-weight: bold;
                text-align: center;
                display: block;
                margin: 30px 0;
            }
            sup{
                font-size: 25px;
                margin-left: 5px;
            }
            .p-15{
                padding: 15px;
            }
            .h2{
                font-size: 15pt;
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.4/dist/JsBarcode.all.min.js"></script>
    </head>
    <body>
        <div class="ticket">
            <div class="header">
                @if($invoice->company->is_retail_reseller && isset($invoice->company->theme_setting['color_logo']))
                    <img src="{{$invoice->company->theme_setting['color_logo']}}" class="img" alt="Logo">
                @else
                    <img src="{{asset('assets/invoices/logo-white.png')}}" class="img" alt="Logo">
                @endif
            </div>
            <div class="store-info">
                <h2 class="text-uppercase">{{ $invoice->company->name }}</h2>
                <span class="">
                    {{ $invoice->company->addr1 }} <br>
                    @if ($invoice->company->addr2)
                        {{ $invoice->company->addr2 }} <br>
                    @endif
                    {{ $invoice->company->city }}, {{ $invoice->company->country }} <br>
                    {{ $invoice->company->zip_code }} <br>

                    @if (!$invoice->company->is_retail_reseller)
                        www.expertshipping.ca
                        @if ($invoice->company->email)
                            <br>
                            {{ $invoice->company->email }}
                        @endif
                    @endif
                    <br> <br>
                </span>
            </div>

            <div class="p-15">
                @if (!$invoice->company->is_retail_reseller)
                    <h2 class="h2">{{ __('Thank you for choosing {appname}!', ['appname' => env('APP_NAME')]) }}</h2>
                @endif
                <p>
                    {{ __('Today, your were Expertly served by :firstname!', ['firstname'=> $invoice->user->first_name]) }}
                </p>
                <table width="100%" class="no-border">
                    <tr class="no-border">
                        <td align="left" class="no-border">
                            <span class="total">
                                {{ \ExpertShipping\Spl\Helpers\Helper::moneyFormat(round($invoice->total, 2), "") }}<sup>$</sup>
                            </span>
                        </td>
                    </tr>
                </table>
                <p class="text-left">
                    {{__("Date")}} : <strong>{{$invoice->created_at->format('d M Y H:i')}}</strong>
                </p>

                <table width="100%">
                    <tr>
                        <td align="left" valign="top" width="40%">
                            <strong>{{ __("PRODUCT") }}</strong>
                        </td>

                        <td align="left" valign="top" width="15%">
                            <strong>{{ __("PRICE") }}</strong>
                        </td>

                        <td align="center" valign="top" width="15%">
                            <strong>{{ __("QTY") }}</strong>
                        </td>

                        <td align="left" valign="top" width="15%">
                            <strong>{{ __("DISCOUNT") }}</strong>
                        </td>

                        <td align="right" valign="top" width="15%">
                            <strong>{{__("TOTAL")}}</strong>
                        </td>
                    </tr>
                </table>

                @foreach ($invoice->details as $detail)
                    <table width="100%">
                        <tr>
                            <td align="left" valign="top" width="40%">
                                @if ($detail->invoiceable_type==="App\Refund")
                                    Refund
                                @endif

                                @if ($detail->invoiceable_type==="App\Shipment" && $detail->invoiceable)
                                    {{ __("Tracking : ") }}
                                    @php
                                        $trackingLink = $detail->invoiceable->trackingLink(
                                            $detail->invoiceable->tracking_number,
                                            $detail->invoiceable->carrier->tracking_link
                                        );
                                    @endphp
                                    <a href="{{$trackingLink}}" target="_blank">
                                        {{ $detail->invoiceable->tracking_number }}
                                    </a>

                                    @if ($detail->invoiceable->carrier->slug==='dhl')
                                        <br>
                                        <a href="https://mydhl.express.dhl/ca/en/mobile.html#/tracking-results/{{$detail->invoiceable->tracking_number}}" target="_blank">
                                            Tracking for Mobile
                                        </a>
                                    @endif
                                @endif

                                @if ($detail->invoiceable_type==="App\Product")
                                    {{ $detail->invoiceable->name }}
                                    @if(str_contains($detail->invoiceable->name, "Drop-Off") && isset($detail->meta_data['tracking_number']))
                                        {{ $detail->meta_data['tracking_number'] }}
                                    @endif
                                @endif

                                @if ($detail->invoiceable_type==="App\Insurance")
                                    {{ __("Insurance Number : ") }} {{ $detail->invoiceable->transaction_number }} <br>
                                    {{ __("Insured Value : ") }} ${{ $detail->invoiceable->declared_value }}
                                @endif
                            </td>

                            <td align="left" valign="top" width="15%">
                                {{ \ExpertShipping\Spl\Helpers\Helper::moneyFormat($detail->price, "") }}
                            </td>

                            <td align="center" valign="top" width="15%">
                                {{ $detail->quantity }}
                            </td>

                            <td align="center" valign="top" width="15%">
                                @if ($detail->discount)
                                    {{ $detail->discount->value }}{{ $detail->discount->type=='percentage'?'%':'$' }}
                                @endif
                            </td>

                            <td align="right" valign="top" width="15%">
                                {{ \ExpertShipping\Spl\Helpers\Helper::moneyFormat(round($detail->total_ht_discount, 2), "$") }}
                            </td>
                        </tr>
                    </table>
                @endforeach

                {{-- TOTAL --}}
                <table width="100%">
                    <tr class="no-border">
                        <td align="left" class="no-border">
                            {{__("Total Discount")}}
                        </td>
                        <td align="right" class="no-border">
                            -{{ \ExpertShipping\Spl\Helpers\Helper::moneyFormat($invoice->discount) }}
                        </td>
                    </tr>
                    <tr class="no-border">
                        <td align="left" class="no-border">
                            {{__("Sub total")}}
                        </td>
                        <td align="right" class="no-border">
                            {{ \ExpertShipping\Spl\Helpers\Helper::moneyFormat($invoice->total - $invoice->sum_tax) }}
                        </td>
                    </tr>
                    @if(isset($invoice->taxes_without_shipment['taxes']) && is_array($invoice->taxes_without_shipment['taxes']))
                        @foreach ($invoice->taxes_without_shipment['taxes'] as $key=>$value)
                            <tr class="no-border">
                                <td align="left" class="no-border">

                                    @if($key==="GST")
                                        TPS
                                    @elseif($key==="QST")
                                        TVQ
                                    @else
                                        {{ $key }}
                                    @endif
                                    {{ $invoice->company->getTaxNumber($key) }}
                                </td>
                                <td align="right" class="no-border">
                                    {{ \ExpertShipping\Spl\Helpers\Helper::moneyFormat($value) }} <br>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    <tr class="no-border">
                        <td align="left" class="no-border">
                            {{__("Total")}}
                        </td>
                        <td align="right" class="no-border">
                            <div class="total-price">
                                {{ \ExpertShipping\Spl\Helpers\Helper::moneyFormat(round($invoice->total, 2)) }}
                            </div>
                        </td>
                    </tr>

                    <br>

                </table>
                <br>

                {{-- TENDER --}}
                <table width="100%">
                    <tr>
                        <td align="left">
                            {{-- <strong>{{__("TENDER")}}</strong> --}}
                            <br>
                            @foreach ($invoice->metadata['payment_details'] as $detail)
                                {{ __($detail['method']) }} <br>
                            @endforeach
                            {{__("Change")}}
                        </td>
                        <td align="right">
                            <strong>{{__("AMOUNT")}}</strong> <br>
                            @foreach ($invoice->metadata['payment_details'] as $detail)
                                {{ \ExpertShipping\Spl\Helpers\Helper::moneyFormat($detail['amount']) }} <br>
                            @endforeach
                            {{\ExpertShipping\Spl\Helpers\Helper::moneyFormat($invoice->change_due)}}
                        </td>
                    </tr>
                </table>
                <br>
                @if($invoice->company->default_store_google_business_url)
                <h2 class="h2">
                    {{ __('Let us know!') }}
                </h2>
                {{-- <p>
                    {{__('Leave us review, this will only take 5 seconds.')}}
                </p> --}}
                <h3>
                    {{  __('How was our service?') }}
                </h3>

                <table width="100%">
                    <tr width="33%">
                        @foreach (App\ClientExperience::all() as $clientExperience)
                        <td>
                            @if ($invoice->clientExperienceDetail)
                                <a href="{{ url("/experience-client/{$invoice->clientExperienceDetail->token}/{$clientExperience->id}") }}" class="button button-experience" target="_blank" rel="noopener">
                                    <img src="{{asset('/assets/review/colors/'.$clientExperience->id)}}.png" alt="{{ $clientExperience->name }}" class="" width="100" style="width: 100px;">
                                </a>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                </table>
                @endif

                <br><br>
                <p class="mt-5">
                    <small class="small">
                        {{ Illuminate\Mail\Markdown::parse($invoice->company->ticket_footer) }}
                    </small>
                </p>

                <br><br>
                <svg id="barcode"></svg>
            </div>
        </div>

        <script>
            JsBarcode("#barcode", "{{$invoice->id}}", {
                // format: "pharmacode",
                lineColor: "#3d454d",
                // width: 4,
                // height: 50,
            });
        </script>
    </body>
</html>
