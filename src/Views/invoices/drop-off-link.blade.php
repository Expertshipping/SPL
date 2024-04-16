@php
    $dropOff = $dropOffs->first();
    App::setLocale($lang??$dropOff->store->local);
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

            .text-justify{
                text-align: justify;
            }
            .size-a1{
                font-size: 18px;
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.4/dist/JsBarcode.all.min.js"></script>
    </head>
    <body>
        <div class="ticket">
            <div class="header">
                @if($dropOff->store->is_retail_reseller && isset($dropOff->store->theme_setting['color_logo']))
                    <img src="{{$dropOff->store->theme_setting['color_logo']}}" class="img" alt="Logo">
                @else
                    <img src="{{asset('assets/invoices/logo-white.png')}}" class="img" alt="Logo">
                @endif
            </div>
            <div class="store-info">
                <h2 class="text-uppercase">{{ $dropOff->store->name }}</h2>
                <p class="centered">
                    {{ $dropOff->store->addr1 }} <br>
                    @if ($dropOff->store->addr2)
                        {{ $dropOff->store->addr2 }} <br>
                    @endif
                    {{ $dropOff->store->city }}, {{ $dropOff->store->state }} {{ $dropOff->store->zip_code }} <br>
                    @if (!$dropOff->store->is_retail_reseller)
                        {{ env('WHITE_LABEL_WEBSITE', 'www.expertshipping.ca') }}
                        @if ($dropOff->store->email)
                            <br> {{ $dropOff->store->email }}
                        @endif
                    @endif
                </p>
            </div>

            <div class="p-15">
                @if (!$dropOff->store->is_retail_reseller)
                    <h2 class="h2">{{ __('Thank you for choosing {appname}!', ['appname' => env('APP_NAME')]) }}</h2>
                @endif
                <p>
                    {{ __('Today, your were Expertly served by :firstname!', ['firstname'=> $dropOff->agent->first_name]) }}
                </p>

                <p class="text-left">
                    {{__("Date")}} : <strong>{{$dropOff->created_at->format('d M Y H:i')}}</strong>
                </p>

                <table width="100%">
                    <tr>
                        <td align="center" valign="top" width="40%">
                            <br>
                            <strong>Preuve de dépot / Proof of Drop-off</strong>
                            <br><br>
                        </td>
                    </tr>
                </table>

                <table width="100%">
                    @foreach ($dropOffs as $dropOff1)
                        <tr>
                            <td align="left" valign="top" width="40%">
                                @if ($dropOff1->group_uuid && $dropOff1->group_items->count()>0)
                                    @foreach ($dropOff1->group_items as $item)
                                        <br>{{$item->carrier->name}} : <a href="{{ $item->tracking_link }}">{{ $item->tracking_number }}</a>
                                    @endforeach
                                @else
                                    <br>{{$dropOff1->carrier->name}} : <a href="{{ $dropOff1->tracking_link }}">{{ $dropOff1->tracking_number }}</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
                <br>

                <br><br>
                @include('invoices.drop-off-footer')
            </div>
        </div>
    </body>
</html>
