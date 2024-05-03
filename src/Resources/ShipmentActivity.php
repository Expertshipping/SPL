<?php

namespace ExpertShipping\Spl\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class ShipmentActivity extends JsonResource
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
            'id'                => $this->id,
            'shipment_id'       => $this->shipment_id,
            'status'            => $this->status,
            'status_code'       => $this->status_code,
            'description'       => $this->description,
            'time'              => $this->time,
            'created_at'        => $this->created_at,
        ];
    }
}
