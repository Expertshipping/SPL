<?php

namespace ExpertShipping\Spl\Controllers;

use ExpertShipping\Spl\Models\LocalInvoice;
use ExpertShipping\Spl\Models\Shipment;
use ExpertShipping\Spl\Models\Spark;
use ExpertShipping\Spl\Services\InvoicePdfMaker;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use PDF;
class CustomsInvoiceController extends Controller
{

    public function download($uuid, Request $request)
    {
        $shipment = Shipment::query()
            ->where('uuid', $uuid)
            ->with(['customInvoice', 'carrier', 'user'])
            ->firstOrFail();

        $signature = asset('static/images/signature'. rand(1, 20) .'.png');
        $pdf = PDF::loadView('spl::pdf.custom.custom-invoice', compact('shipment', 'signature'));

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $pdf->setHttpContext($context);

        return $pdf->download('invoice.pdf');
    }
}
