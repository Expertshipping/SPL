<?php

namespace ExpertShipping\Spl\Actions;

use ExpertShipping\Spl\Models\LocalInvoice;

class UpdateInvoiceStatusAction
{
    protected $handlers;

    public function __construct(
        InvoiceCanceled $invoiceCanceled,
        InvoicePayed $invoicePayed,
        InvoiceRefunded $invoiceRefunded,
    ) {
        $this->handlers = [
            'Pay' => $invoicePayed,
            'Refund' => $invoiceRefunded,
            'Cancel' => $invoiceCanceled,
        ];
    }

    public function handle(LocalInvoice $invoice, string $status): void
    {
        if (!isset($this->handlers[$status])) {
            throw new \InvalidArgumentException("Invalid status: $status");
        }
        $this->handlers[$status]->handle([$invoice]);
    }
}
