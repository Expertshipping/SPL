<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Transaction extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'staff' => $this->user->name??'User Not Found',
            'store' => $this->company->name??'Company Not Found',
            'device' => $this->cashRegisterSession?$this->cashRegisterSession->cashRegister->name:null,
            'date_time' => $this->created_at->format("d/m/Y H:i"),
            'discount' => $this->discount,
            'discount_reason' => $this->discount_reason,
            'total' => $this->total,
            'tender' => $this->metadata['payment_details']??null,
            'change' => isset($this->metadata['payment_details'])?round(collect($this->metadata['payment_details'])->sum('amount') - $this->total, 2):null,
            'details' => new TransactionDetailCollection($this->details),
            'all_tender' => $this->allTender()
        ];
    }

    private function allTender(){
        return [
            [
                'method' => 'Card',
                'amount' => $this->methodAmount('Card')+$this->methodAmount('DebitCard')+$this->methodAmount('CreditCard'),
            ],
            // [
            //     'method'=>'DebitCard',
            //     'amount' => $this->methodAmount('DebitCard'),
            // ],
            // [
            //     'method' => 'CreditCard',
            //     'amount' => $this->methodAmount('CreditCard'),
            // ],
            [
                'method' => 'Cash',
                'amount' => $this->methodAmount('Cash'),
            ],
            [
                'method' => 'E-Transfert',
                'amount' => $this->methodAmount('E-Transfert'),
            ],
            [
                'method' => 'MailBox',
                'amount' => $this->methodAmount('MailBox'),
            ],
        ];
    }

    private function methodAmount($method){
        $amount = 0;
        if(!isset($this->metadata['payment_details'])){
            return $amount;
        }

        foreach ($this->metadata['payment_details'] as $detail) {
            if($detail['method']===$method){
                $amount = $detail['amount'];
                break;
            }
        }

        return (float) $amount;
    }
}
