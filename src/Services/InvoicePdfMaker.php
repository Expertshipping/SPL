<?php

namespace ExpertShipping\Spl\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use ExpertShipping\Spl\Models\LocalInvoice;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class InvoicePdfMaker
{
    protected LocalInvoice $invoice;

    public function downloadAs(LocalInvoice $invoice)
    {
        App::setLocale($invoice->company->local);
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
        $pdf = Pdf::loadView('spl::invoices.invoice.invoice', [
            'invoice' => $this->invoice,
            'detailsTaxes' => $this->invoice->details_taxes,
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
        return View::make('spl::invoices.invoice.invoice', [
            'invoice' => $this->invoice,
            'detailsTaxes' => $this->invoice->details_taxes,
        ]);
    }
}
