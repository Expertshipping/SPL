<div class="page-2">
    <table width="100%" class="table-1">
        <tr>
            <th><div class="text-white">{{ __('#BOL/Tracking') }}</div></th>
            <th><div class="text-white">{{ __('#Customer Ref.') }}</div></th>
            <th><div class="text-white">{{ __('Date') }}</div></th>
            <th><div class="text-white">{{ __('Charges') }}</div></th>
            <th><div class="text-white">{{ __('Authorization') }}</div></th>
            <th><div class="text-white">{{ __('Status') }}</div></th>
            <th><div class="text-white">{{ __('Service') }}</div></th>
        </tr>
        @foreach($invoice->chargeable_details as $detail)
            @if($detail->invoiceable_type === 'App\Shipment')
                <tr>
                    <td>{{ $detail->invoiceable->tracking_number }}</td>
                    <td>{{ $detail->invoiceable->reference_value }}</td>
                    <td>{{ $detail->invoiceable->date }}</td>
                    <td>{{ SplMoney::format($detail->total) }}</td>
                    <td>{{ $detail->invoiceable->authorization }}</td>
                    <td>{{ \ExpertShipping\Spl\Models\Shipment::STATUSES[$detail->invoiceable->type] ?? 'N/A' }}</td>
                    <td>{{ $detail->invoiceable->getService()->name }}</td>
                </tr>
            @endif

            @if($detail->invoiceable_type === 'App\ShipmentSurcharge')
                <tr>
                    <td>{{ $detail->invoiceable->shipment->tracking_number }}</td>
                    <td></td>
                    <td>{{ $detail->invoiceable->date }}</td>
                    <td>{{ SplMoney::format($detail->total) }}</td>
                    <td></td>
                    <td>{{ \ExpertShipping\Spl\Models\Shipment::STATUSES[$detail->invoiceable->shipment->type] ?? 'N/A' }}</td>
                    <td>Surcharge : {{ $detail->invoiceable->name }} : {{ $detail->invoiceable->description }}</td>
                </tr>
            @endif
        @endforeach

        <tr>
            <th colspan="7" align="right">
                <div class="text-white text-right">
                    {{ __('Total Amount Charged') }} : {{ SplMoney::format($invoice->total) }}
                </div>
            </th>
        </tr>
    </table>

    <hr/>
    <br>
    <br>
    <br>
    <br>
    <br>
</div>
