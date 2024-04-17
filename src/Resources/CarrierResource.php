<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CarrierResource extends JsonResource
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
            'slug' => $this->slug,
            'key' => $this->key,
            'account_number' => $this->account_number,
            'username' => $this->username,
            'password' => $this->password,
            'meter_number' => $this->meter_number,
            'user' => $this->whenLoaded('user'),
            'services' => $this->whenLoaded('services'),
            'pickups' => $this->whenLoaded('pickups'),
            'paperless'=> $this->paperless,
            'logo' => $this->image_url,
        ];
    }
}
