<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CashRegister extends JsonResource
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
            'cash_register_sessions' => $this->whenLoaded('cashRegisterSessions', function() {
                return new CashRegisterSessionCollection($this->cashRegisterSessions, []);
            }),
            'company_id' => $this->comment,
            'created_at' => $this->created_at,
            'description' => $this->description,
            'name' => $this->name,
        ];
    }
}
