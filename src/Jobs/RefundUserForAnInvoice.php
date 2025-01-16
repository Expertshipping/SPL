<?php

namespace ExpertShipping\Spl\Jobs;

use ExpertShipping\Spl\Models\LocalInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefundUserForAnInvoice implements ShouldQueue
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
     * @throws \Exception
     */
    public function handle()
    {
        try {
            $invoice = $this->invoice;
            if(! $invoice || ! $invoice->isCancelable()) {
                return;
            }

            $user = $invoice->user;

            if(!$invoice->provider_id && $user->company && !$user->company->instant_payment) {
                $invoice->refunded_at = now();
                $invoice->save();
                return;
            }

            $stripeInvoice = $user->findInvoice($invoice->provider_id);
            if(! $stripeInvoice) {
                throw new \Exception("The invoice does not exists invoice_id: {$invoice->id}");
            }

            $refund = $user->refund($stripeInvoice->payment_intent);

            if($refund->status === 'succeeded') {
                $invoice->refunded_at = now();
                $invoice->save();
            }
        } catch(\Exception $e) {
            throw $e;
        }
    }
}
