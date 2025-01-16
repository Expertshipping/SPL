<?php

namespace ExpertShipping\Spl\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ExpertShipping\Spl\Models\LocalInvoice;

class SetInvoiceAsPaid implements  ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param  LocalInvoice  $invoice
     */
    public function __construct(public LocalInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $invoice = $this->invoice;
        $invoice->paid_at = now();
        $invoice->refunded_at = null;
        $invoice->bulkInvoices->each(function($in){
            $in->paid_at = now();
            $in->refunded_at = null;
            $in->save();
        });
        $invoice->save();
    }
}
