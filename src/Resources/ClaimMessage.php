<?php

namespace ExpertShipping\Spl\Resources;

use ExpertShipping\Spl\Models\Shipment as AppShipment;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimMessage extends JsonResource
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
            'id'=> $this->id,
            'created_at'=> $this->created_at->format("d/m/Y H:i"),
            'body'=> $this->body,
            'user'=> $this->user,
        ];
    }
}
