@if($invoice->company->invoice_printing_option === 'snapshot')
    @include('spl::invoices.invoice.snapshot')
@endif

@if($invoice->company->invoice_printing_option === 'detailed')
    @include('spl::invoices.invoice.detailed')
@endif
