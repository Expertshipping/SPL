<?php

namespace ExpertShipping\Spl\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use ExpertShipping\Spl\Models\LocalInvoice;
use Illuminate\Support\Facades\View;
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
        return 'invoice-' . $this->invoice->id;
    }

    protected function pdf()
    {
//        if (!defined('DOMPDF_ENABLE_AUTOLOAD')) {
//            define('DOMPDF_ENABLE_AUTOLOAD', false);
//            define('DOMPDF_ENABLE_PHP', true);
//        }
//
//        $options = new Options();
//        $options->set('isRemoteEnabled', true);
//        $pdf = new Dompdf($options);
//
//        $context = stream_context_create([
//            'ssl' => [
//                'verify_peer' => false,
//                'verify_peer_name' => false,
//                'allow_self_signed' => true
//            ]
//        ]);
//
//        $pdf->setHttpContext($context);
//
//        $pdf->setPaper('a4');
//        $pdf->loadHtml($this->view()->render());
//        $pdf->render();
//
//        return $pdf->output();

        $pdf = Pdf::loadView('spl::invoices.snapshot.snapshot', [
            'invoice' => $this->invoice,
        ]);

        return $pdf->download();
    }
}
