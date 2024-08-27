<?php

namespace ExpertShipping\Spl\Controllers;

use ExpertShipping\Spl\Models\LocalInvoice;
use ExpertShipping\Spl\Models\Spark;
use ExpertShipping\Spl\Services\InvoicePdfMaker;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
class InvoiceController extends Controller
{

    public function download(Request $request, $id)
    {
        $id = decrypt($id);
        $invoice = LocalInvoice::findOrFail($id);

        return app(InvoicePdfMaker::class)->downloadAs($invoice);
    }
}
