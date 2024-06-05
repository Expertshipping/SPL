<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>ExpertShipping</title>
        <style>
            * {
                font-size: 12px;
                font-family: sans-serif;
                line-height: 1.3;
            }
            td,
            th,
            tr,
            table {
                border-top: 1px solid black;
                border-collapse: collapse;
            }

            .centered {
                text-align: center;
                align-content: center;
            }

            .ticket {
                width: 100%;
                max-width: 100%;
                text-align: center;
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

            table.no-border, table.no-border tr, table.no-border td{
                border-top: 0 !important;
            }

            .no-border{
                border-top: 0 !important;
            }

            .total-price{
                font-size: 20px;
                font-weight: bold;
            }

            .table-al, .table-al td,.table-al tr{
                text-align: left;
                border: 0;
            }

            .text-left{
                text-align: left;
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
            @php
                $domain = parse_url(env('APP_URL'))['host'];
            @endphp
            @if($dropOff->store->is_retail_reseller && isset($dropOff->store->theme_setting['color_logo']))
                <img src="{{$dropOff->store->theme_setting['color_logo']}}" class="img" alt="Logo">
            @else
                <img src="{{asset('domains/'.$domain.'/logo-black.png')}}" class="img" alt="Logo">
            @endif
            <p class="centered">
                {{ $dropOff->store->name }} <br>
                {{ $dropOff->store->addr1 }} <br>
                @if ($dropOff->store->addr2)
                    {{ $dropOff->store->addr2 }} <br>
                @endif
                {{ $dropOff->store->city }}, {{ $dropOff->store->state }} {{ $dropOff->store->zip_code }} <br>
                @if (!$dropOff->store->is_retail_reseller)
                    {{ (request()->platformDomain?->domain ?? 'www.expertshipping.ca') }}
                    @if ($dropOff->store->email)
                        <br>{{ $dropOff->store->email }}
                    @endif
                @endif
            </p>
            <br><br>

            <table width="100%">
                <tr>
                    <td align="left">
                        <br>
                        <strong>
                            @php
                                App::setLocale($dropOff->store->local);
                            @endphp
                        </strong>
                        <strong>
                            {{$dropOff->created_at->format('Y/m/d H:i:s')}}
                        </strong>
                    </td>
                    <td align="right">
                        <br>
                        Agent : {{ $dropOff->agent->first_name }}
                    </td>
                </tr>
            </table>

            <br>

            <table width="100%">
                <tr>
                    <td align="center" valign="top" width="40%">
                        <br>
                        <strong class="size-a1">Preuve de dépot / Proof of Drop-off</strong>
                        <br>
                        <br>
                    </td>
                </tr>
            </table>

            <table width="100%">
                <tr>
                    <td align="left" valign="top" width="40%">
                        @if ($dropOff->group_uuid && $dropOff->group_items->count()>0)
                            @foreach ($dropOff->group_items as $item)
                                <br>{{$item->carrier->name}} : <a href="{{ $item->tracking_link }}">{{ $item->tracking_number }}</a>
                            @endforeach
                        @else
                            <br>{{$dropOff->carrier->name}} : <a href="{{ $dropOff->tracking_link }}">{{ $dropOff->tracking_number }}</a>
                        @endif
                    </td>
                </tr>
            </table>

            <br>
            <br>

            @include('invoices.drop-off-footer')
        </div>
    </body>
</html>
