<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SoldeTransaction extends JsonResource
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
            'id' => $this->id,
            'user' => $this->whenLoaded('user', new User($this->user)),
            'company' => $this->whenLoaded('company', new Company($this->company)),
            'amount' => $this->amount,
            'type' => $this->display_type,
            'created_at' => $this->created_at,
            'details' => $this->soldeable_details,
        ];
    }
}
