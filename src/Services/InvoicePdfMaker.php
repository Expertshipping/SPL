<?php

namespace ExpertShipping\Spl\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use ExpertShipping\Spl\Models\LocalInvoice;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\Response;

class InvoicePdfMaker
{
    protected LocalInvoice $invoice;

    public function downloadAs(LocalInvoice $invoice)
    {
        $this->invoice = $invoice;
//        return $this->view();
        return new Response($this->pdf(), 200, [
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename="' . $this->getFileName() . '.pdf"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Type' => 'application/pdf',
            'X-Vapor-Base64-Encode' => 'True',
        ]);
    }

    protected function getFileName()
    {
        return 'invoice-' . $this->invoice->company->invoice_printing_option . '-'. $this->invoice->id;
    }

    protected function pdf()
    {
        $attribute = $this->invoice->company->is_retail_reseller ? 'retail_reseller_rate_details' : 'rate_details';

        $pdf = Pdf::loadView('spl::invoices.invoice.invoice', [
            'invoice' => $this->invoice,
            'detailsTaxes' => $this->invoice->details_taxes,
            'rateDetailsAttribute' => $attribute,
        ]);

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $pdf->setHttpContext($context);

        return $pdf->download();
    }

    protected function view()
    {
        $attribute = $this->invoice->company->is_retail_reseller ? 'retail_reseller_rate_details' : 'rate_details';

        return View::make('spl::invoices.invoice.invoice', [
            'invoice' => $this->invoice,
            'detailsTaxes' => $this->invoice->details_taxes,
            'rateDetailsAttribute' => $attribute,
        ]);
    }
}
