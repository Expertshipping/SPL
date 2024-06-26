<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Company extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'agent' => $this->whenLoaded('agent'),
            'store' => $this->whenLoaded('store'),
            'user' => $this->whenLoaded('user'),
        ];
    }
}
