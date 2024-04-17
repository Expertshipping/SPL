<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayoutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'company_id' => $this->company_id,
            'paid_amount' => $this->paid_amount,
            'payment_method' => $this->payment_method,
            'bank_name' => $this->bank_name,
            'status' => $this->status,
            'account_holder_name' => $this->account_holder_name,
            'account_number' => $this->account_number,
            'transit_number' => $this->transit_number,
            'created_at' => $this->created_at->format('d/m/Y H:i'),
        ];
    }
}
