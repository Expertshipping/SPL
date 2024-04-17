<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FreeLabelCode extends JsonResource
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
            'reason' => $this->reason,
            'tracking_number' => $this->tracking_number,
            'shipment' => $this->shipment,
            'company' => $this->company,
            'user' => $this->user,
            'code' => $this->code,
            'date' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
