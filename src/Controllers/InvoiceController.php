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
        // TODO: add auth middleware
        //        if($request->user()->is_admin){
        //            $invoice = LocalInvoice::where('id', $id)
        //                ->with('bulkInvoices')
        //                ->firstOrFail();
        //        }else{
        //            $invoice = $request->user()
        //                ->company
        //                ->localInvoices()
        //                ->where('id', $id)
        //                ->with('bulkInvoices')
        //                ->firstOrFail();
        //        }

        $invoice = LocalInvoice::query()
            ->where('id', $id)
//            ->with('details.invoiceable.service')
            ->firstOrFail();

        return app(InvoicePdfMaker::class)->downloadAs($invoice);
    }
}
