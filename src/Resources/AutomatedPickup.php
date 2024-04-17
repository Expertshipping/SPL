<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class AutomatedPickup extends JsonResource
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
            'uuid' => $this->uuid,
            'carrier_id' => $this->carrier_id,
            'pickup_service_code' => $this->pickup_service_code,
            'destination_country' => $this->destination_country,
            'total_weight' => $this->total_weight,
            'pickup_company_name' => $this->pickup_company_name,
            'pickup_contact_name' => $this->pickup_contact_name,
            'pickup_addr1' => $this->pickup_addr1,
            'pickup_addr2' => $this->pickup_addr2,
            'pickup_city' => $this->pickup_city,
            'pickup_state' => $this->pickup_state,
            'pickup_code' => $this->pickup_code,
            'pickup_country' => $this->pickup_country,
            'pickup_phone' => $this->pickup_phone,
            'close_time' => $this->close_time,
            'ready_time' => $this->ready_time,
            'pickup_quantity' => $this->pickup_quantity,
            'frequency' => $this->frequency,
            'day' => $this->day,
            'time' => $this->time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'carrier_name' => $this->carrier->name,
            'carrier_slug' => $this->carrier->slug,
            'company_name' => $this->company->name,
            'user_name' => $this->user->name,
        ];
    }
}
