<?php
namespace ExpertShipping\Spl\Jobs;

use ExpertShipping\Spl\Models\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Laravel\Cashier\Payment;

class ChargeUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $amount;
    protected $currency;
    protected $description;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $amount, $currency, $description)
    {
        $this->user = $user;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->description = $description;
    }

    /**
     * @return Payment|void
     * @throws IncompletePayment
     * @throws \Exception
     */
    public function handle()
    {
        $user = $this->user;

        if(($user->company && $user->company->instant_payment) || request('payment') === 'pay-now'){
            if (is_null($user->defaultPaymentMethod()->asStripePaymentMethod())) {
                throw new \Exception("Payment method doesn't exists");
            }

            return $user->charge(
                Money::fromCurrencyAmount($this->amount)->inCent(),
                $user->defaultPaymentMethod()->asStripePaymentMethod(),
                ['description' => $this->description]
            );
        }
    }
}
