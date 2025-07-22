<?php

namespace ExpertShipping\Spl\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class Invoice extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'date'              => $this->created_at->format("D F Y g:i"),
            'status'            => $this->status,
            'paid_at'           => $this->paid_at?->format("D F Y g:i"),
            'currency'          => request()->platformCountry?->currency ?? 'CAD',
            'company'           => new Company($this->whenLoaded('company')),

            'payment_gateway'   => $this->whenLoaded('paymentGateway'),
            'total'             => $this->total,
            'claim'             => $this->claim,
            'bulkInvoices'      => $this->whenLoaded('bulkInvoices'),
            'bulk_id'           => $this->bulk_id,
        ];
    }
}
