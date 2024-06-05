<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Carrier extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'tracking_link' => $this->tracking_link,
            'paperless' => $this->paperless,
            'logo' => $this->image_url,
            'pickup_api_or_email' => $this->active_platform_country?->pivot?->pickup_api_or_email,
            'has_ground_service' => $this->active_platform_country?->pivot?->has_ground_service,
        ];
    }
}
