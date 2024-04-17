<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class CarrierPickup extends JsonResource
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
            'carrier' => $this->whenLoaded('carrier'),
            'user' => $this->whenLoaded('user'),
            'company' => $this->whenLoaded('company'),
            'date' => $this->date->format('Y-m-d H:i'),
        ];
    }
}
