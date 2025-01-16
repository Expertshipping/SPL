<?php

namespace ExpertShipping\Spl\Jobs;

use ExpertShipping\Spl\Models\LocalInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetInvoiceAsCancelled implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected LocalInvoice $invoice)
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
        if(!$invoice->provider_id && $this->invoice->user->company) {
            $invoice->paid_at = null;
            $invoice->refunded_at = null;
            $invoice->canceled_at = today();
            $invoice->save();
        }else{
            throw new \Exception("The invoice can't be cancelled");
        }
    }
}
