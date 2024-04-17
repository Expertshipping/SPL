<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class CashRegisterSessionTwo extends JsonResource
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
            'cash_register_id' => $this->cash_register_id,
            'cash_register' => $this->whenLoaded('cashRegister'),
            'closing_amount' => $this->closing_amount,
            'created_at' => $this->created_at,
            'closed_at' => $this->closed_at,
            'invoices' => $this->whenLoaded('invoicesWithoutDropOff'),
            'manager_id' => $this->manager_id,
            'opening_amount' => $this->opening_amount,
            'status' => $this->status,
            'updated_at' => $this->updated_at,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', optional($this->whenLoaded('user'))->setAppends([])),
            'totalCash' => $this->counted_cash,
            'totalCard' => $this->counted_card,
            'totalEtransfert' => $this->counted_etransfert,
            'totalDiscount' => $this->total_discount,
            'totalVariance' => $this->total_variance,
            'detailedTotal' => $this->detailed_total,
            'total' => $this->invoicesWithoutDropOff->sum('total'),
            'notes' => $this->notes,
            'manager_comment' => $this->manager_comment,
            'manager_validation' => $this->manager_validation,
            'manager_validation_amount' => $this->manager_validation_amount,
        ];
    }
}
