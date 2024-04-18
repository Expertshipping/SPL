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

        @foreach($invoice->details as $detail)
            @if($detail->invoiceable instanceof \App\Shipment)
                <tr>
                    <td>{{ $detail->invoiceable->tracking_number }}</td>
                    <td>{{ $detail->invoiceable->reference_value }}</td>
                    <td>{{ $detail->invoiceable->date }}</td>
                    <td>{{ $detail->invoiceable->rate }}</td>
                    <td>{{ $detail->invoiceable->authorization }}</td>
                    <td>{{ \App\Shipment::STATUSES[$detail->invoiceable->type] ?? 'N/A' }}</td>
                    <td>{{ $detail->invoiceable->getService()->name }}</td>
                </tr>
            @endif
        @endforeach

        <tr>
            <th colspan="7" align="right">
                <div class="text-white">
                    {{ __('Total Amount Charged') }} : {{ $invoice->total }}
                </div>
            </th>
        </tr>
    </table>

    <hr/>
</div>
