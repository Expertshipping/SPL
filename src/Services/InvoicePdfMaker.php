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
        $pdf = Pdf::loadView('spl::invoices.invoice.invoice', [
            'invoice' => $this->invoice,
            'detailsTaxes' => $this->invoice->details_taxes,
        ]);

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
