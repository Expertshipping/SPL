<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class CashRegisterSession extends JsonResource
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
            'closing_amount' => $this->closing_amount,
            'created_at' => $this->created_at,
            'closed_at' => $this->closed_at,
            'manager_id' => $this->manager_id,
            'opening_amount' => $this->opening_amount,
            'status' => $this->status,
            'updated_at' => $this->updated_at,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', optional($this->whenLoaded('user'))->setAppends([])),
            'totalCash' => $this->counted_cash??0,
            'totalCard' => $this->counted_card??0,
            'totalEtransfert' => $this->counted_etransfert??0,
            'totalGiftCard' => $this->counted_gift_card??0,
            'totalAnytimeMailbox' => $this->counted_anytime_mailbox??0,
        ];
    }
}
