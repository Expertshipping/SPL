<div class="page-3">
    @foreach($invoice->chargeable_details as $detail)
        @if($detail->invoiceable instanceof \App\Shipment)
            @include('spl::invoices.invoice.pages.partials.chargeable-detail', [
                    'detail' => $detail,
                    'invoice' => $invoice,
                    'shipment' => $detail->invoiceable
            ])
        @endif

        @if($detail->invoiceable instanceof \App\ShipmentSurcharge)
            @include('spl::invoices.invoice.pages.partials.chargeable-detail', [
                    'detail' => $detail,
                    'invoice' => $invoice,
                    'shipment' => $detail->invoiceable->shipment,
                    'surcharge' => $detail->invoiceable
            ])
        @endif
    @endforeach
</div>
