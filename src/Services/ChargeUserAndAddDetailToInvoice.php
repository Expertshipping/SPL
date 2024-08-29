<?php

namespace ExpertShipping\Spl\Services;

use ExpertShipping\Spl\Models\Company;
use ExpertShipping\Spl\Models\SoldeTransaction;
use ExpertShipping\Spl\Models\User;
use Illuminate\Database\Eloquent\Model;

class ChargeUserAndAddDetailToInvoice
{

    public function charge(User $user, Model $chargeable, $charge, $companyId = null)
    {
        $detail = $user->createInvoiceForUser($chargeable, $charge, $companyId);

        if(env('WHITE_LABEL_COUNTRY', 'CA') === 'MA'){
            $chargeable->update([
                'is_paid' => false,
            ]);
        }
        else{
            try {
                $company = $companyId ? Company::findOrFail($companyId) : $user->company;

                $solde = $company->solde;
                $paymentDetail = [];

                if ($solde >= $charge) {
                    $newSolde = $solde - $charge;
                    $paymentDetail['solde'] = $charge;
                    $usedSolde = $charge;
                    $paymentMethod = 'Solde';

                    $soldeTransaction = SoldeTransaction::create([
                        'user_id'           => $user->id,
                        'company_id'        => $company->id,
                        'amount'            => $usedSolde,
                        'type'              => SoldeTransaction::TYPES['PURCHASE'],
                        'soldeable_id'      => $chargeable->id,
                        'soldeable_type'    => get_class($chargeable),
                    ]);

                    $detail->update([
                        'meta_data' => [
                            ...($detail->meta_data ?? []),
                            'payment_details' => $paymentDetail,
                            'payment_intent_id' => $soldeTransaction?->id,
                            'payment_transaction_number' => $soldeTransaction?->id,
                            'payment_method' => $paymentMethod,
                            'payment_date' => now()->toDateTimeString(),
                        ]
                    ]);

                    if($newSolde !== $solde){
                        $company->solde = $newSolde;
                        $company->save();
                    }
                }
                elseif($company->instant_payment || request()->payment === 'pay-now'){
                    $chargeable = $chargeable->fresh();
                    $description = 'Payment for ' . get_class($chargeable) . ' '.$chargeable->id;
                    if(get_class($chargeable) === 'App\Shipment'){
                        $description = "Shipment {$chargeable->tracking_number}";
                    }

                    $paymentIntent = (new \ExpertShipping\Spl\Jobs\ChargeUser(
                        $user,
                        $charge,
                        env('WHITE_LABEL_CURRENCY', 'CAD'),
                        $description,
                    ))->handle();

                    $paymentDetail['stripe'] = $charge;

                    $detail->update([
                        'meta_data' => [
                            ...($detail->meta_data ?? []),
                            'payment_details' => $paymentDetail,
                            'payment_intent_id' => $paymentIntent?->id,
                            'payment_transaction_number' => $paymentIntent?->id,
                            'payment_method' => 'Credit card',
                            'payment_date' => now()->toDateTimeString(),
                        ]
                    ]);
                }
            } catch (\Exception $e) {
                if(get_class($chargeable) === 'App\Shipment'){
                    $chargeable->update([
                        'is_paid' => false,
                    ]);
                }
            }
        }

        $detail->invoice->updateTotal(false);
    }
}
