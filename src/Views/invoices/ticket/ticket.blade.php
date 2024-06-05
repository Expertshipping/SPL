@php
    $domain = parse_url(env('APP_URL'))['host'];
@endphp
@if($invoice->company->is_retail_reseller && isset($invoice->company->theme_setting['color_logo']))
    <img src="{{$invoice->company->theme_setting['color_logo']}}" class="img" alt="Logo">
@else
    <img src="{{asset('domains/'.$domain.'/logo-black.png')}}" class="img" alt="Logo">
@endif
<p class="centered">
    {{ $invoice->company->name }} <br>
    {{ $invoice->company->addr1 }} <br>
    @if ($invoice->company->addr2)
        {{ $invoice->company->addr2 }} <br>
    @endif
    {{ $invoice->company->city }}, {{ $invoice->company->country }} <br>
    {{ $invoice->company->zip_code }} <br>
    @if (!$invoice->company->is_retail_reseller)
        {{ (request()->platformDomain?->domain ?? 'www.expertshipping.ca') }}
        @if ($invoice->company->email)
            <br>
            {{ $invoice->company->email }}
        @endif
    @endif
</p>
<br><br>

<table width="100%">
    <tr>
        <td align="left">
            <strong>
                @php
                    App::setLocale($invoice->company->local);
                @endphp
                {{-- {{ __("Receipt of purshare") }} --}}
            </strong>
            <br>
            {{ __("Staff") }} <br>
            {{ __("Device") }}
        </td>
        <td align="right">
            <strong>
                {{$invoice->created_at->format('Y/m/d H:i:s')}}
            </strong>
            <br><br>
            {{ $invoice->user->first_name }}
            <br>
            {{ $invoice->cashRegisterSession->cashRegister->name }}
        </td>
    </tr>
</table>

<br>

<table width="100%">
    <tr>
        <td align="left" valign="top" width="40%">
            <strong>{{ __("PRODUCT") }}</strong>
        </td>

        <td align="left" valign="top" width="20%">
            <strong>{{ __("PRICE") }}</strong>
        </td>

        <td align="center" valign="top" width="20%">
            <strong>{{ __("QTY") }}</strong>
        </td>

        <td align="right" valign="top" width="20%">
            <strong>{{__("TOTAL")}}</strong>
        </td>
    </tr>
</table>

@foreach ($invoice->details->where('pos', true) as $detail)
    <table width="100%">
        <tr>
            <td align="left" valign="top" width="40%">
                @if ($detail->invoiceable_type==="App\Refund")
                    Refund
                @endif

                @if ($detail->invoiceable_type==="App\Shipment")
                @php
                    $shipment = App\Shipment::whereId($detail->invoiceable_id)->withService()->first();
                @endphp
                {{ $shipment->carrier->name }}
                ({{ $shipment->service->name }}) : <br>
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
                    {{ __("Insured Value : ") }} {{ request()->platformCountry?->currency }} {{ $detail->invoiceable->declared_value }}
                @endif
            </td>

            <td align="left" valign="top" width="20%">
                {{ Helper::moneyFormat($detail->price, "") }}
            </td>

            <td align="center" valign="top" width="20%">
                {{ $detail->quantity }}
            </td>

            <td align="right" valign="top" width="20%">
                {{ Helper::moneyFormat(round($detail->price*$detail->quantity, 2), "") }}
            </td>
        </tr>
    </table>
@endforeach

{{-- TOTAL --}}
<table width="100%">
    @if ($invoice->agentTip)
        <tr class="no-border">
            <td align="left" class="no-border">
                {{__("Tip")}}
            </td>
            <td align="right" class="no-border">
                {{ Helper::moneyFormat($invoice->agentTip->tip_amount, request()->platformCountry?->currency) }}
            </td>
        </tr>
    @endif
    <tr class="no-border">
        <td align="left" class="no-border">
            {{__("Total Discount")}}
        </td>
        <td align="right" class="no-border">
            {{ Helper::moneyFormat($invoice->discount, request()->platformCountry?->currency) }}
        </td>
    </tr>

    <tr class="no-border">
        <td align="left" class="no-border">
            {{__("Sub total")}}
        </td>
        <td align="right" class="no-border">
            {{ Helper::moneyFormat($invoice->total - $invoice->sum_tax, request()->platformCountry?->currency) }}
        </td>
    </tr>
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
                {{$invoice->company->getTaxNumber($key) }}
            </td>
            <td align="right" class="no-border">
                {{ Helper::moneyFormat($value, request()->platformCountry?->currency) }} <br>
            </td>
        </tr>
    @endforeach

    <tr class="no-border">
        <td align="left" class="no-border">
            {{__("Total")}}
        </td>
        <td align="right" class="no-border">
            <div class="total-price">
                {{ Helper::moneyFormat(round($invoice->total+($invoice->agentTip->tip_amount ?? 0), 2), request()->platformCountry?->currency) }}
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
                {{ __($detail['method']) }}
                @if ($detail['method']==='GiftCard')
                    : {{$detail['data']['code']}}
                @endif
                @if ($detail['method']==='Card' && isset($detail['transaction_number']))
                    : {{$detail['transaction_number']}}
                @endif
                <br>
            @endforeach
            {{__("Change")}}
        </td>
        <td align="right">
            <strong>{{__("AMOUNT")}}</strong> <br>
            @foreach ($invoice->metadata['payment_details'] as $detail)
                {{ Helper::moneyFormat($detail['amount'], request()->platformCountry?->currency) }} <br>
            @endforeach
            {{Helper::moneyFormat($invoice->change_due, request()->platformCountry?->currency)}}
        </td>
    </tr>
</table>
<br>
@if($invoice->metadata['payment_details'])
    @foreach ($invoice->metadata['payment_details'] as $detail)
        @if (isset($detail['data']))
            <div>
                <table width="100%" class="table-al">
                    <tr width="50%"><td>{{__("TYPE")}}:</td><td width="50%">{{ Illuminate\Support\Str::upper(__($detail['data']['TxnName'])) }}</td></tr>
                    <tr><td>{{__("ACCT")}}:</td><td>{{ $detail['data']['CardName'] }} $ {{ $detail['data']['Amount'] }}</td></tr>
                    <tr><td>{{__("CARDNUMBER")}}:</td><td>{{ $detail['data']['Pan'] }}</td></tr>
                    <tr><td>{{__("DATE/TIME")}}:</td><td>{{ $detail['data']['TransDate'] }} {{ $detail['data']['TransTime'] }}</td></tr>
                    <tr><td>{{__("REFERENCE #")}}:</td><td>{{ $detail['data']['ReferenceNumber'] }} {{ $detail['data']['SwipeIndicator']==='Q'?'C':$detail['data']['SwipeIndicator'] }}</td></tr>
                    <tr><td>{{__("AUTH. #")}}:</td><td>{{ $detail['data']['AuthCode'] }}</td></tr>
                </table>
            </div>
            <br>
            <div class="text-left">
                {{ Illuminate\Support\Str::upper($detail['data']['AppLabel']) }} <br>
                {{ $detail['data']['Aid'] }} <br>
                {{ $detail['data']['TvrArqc'] }} {{ $detail['data']['Tsi']??'' }} <br> <br>
            </div>
            <br>

            <div>
                @if (isset($detail['data']['ErrorCode']))
                    {{ $detail['data']['ErrorCode']!=='616'?$detail['data']['IsoResponseCode'].'/'.$detail['data']['ResponseCode']:'' }} {{__("TRANSACTION NOT COMPLETED")}}
                @else
                    {{$detail['data']['IsoResponseCode']}}/{{$detail['data']['ResponseCode']}} {{ __("APPROVED – THANK YOU") }}
                @endif
            </div>
            <br><br>
        @endif
    @endforeach
@endif
