<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class Integration extends JsonResource
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
            'id'        => $this->id,
            'platform'  => $this->whenLoaded('platform'),
            'store_name'=> $this->store_name,
            'store_url' => $this->store_url,
            'status'    => $this->status,
            'store_token'    => $this->store_token,
            'checkout'    => $this->checkout,
            'checkout_api_supported'    => $this->checkout_api_supported,
            'token'    => $this->meta_data['api_token']??'',
        ];
    }
}
