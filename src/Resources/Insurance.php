<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class Insurance extends JsonResource
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
            'id'                    => $this->id,
            'carrier_id'            =>  $this->carrier_id,
            'carrier_name'          =>  $this->carrier->name,
            'service_id'            =>  $this->service_id,
            'service_name'          =>  $this->service->name??'',
            'tracking_number'       => $this->tracking_number,
            'ship_date'             => $this->ship_date->format("d/m/Y"),
            'declared_value'        => $this->declared_value,
            'ship_from'             => $this->ship_from,
            'ship_to'               => $this->ship_to,
            'name'                  => $this->name,
            'phone'                 => $this->phone,
            'email'                 => $this->email,
            'transaction_number'    => $this->transaction_number,
            'status'                => $this->status,
            'price'                 => $this->price,
            'created_at'            => $this->created_at->format("d/m/Y"),
            'paid'                  => $this->whenLoaded('invoiceDetail'),
            'shipment'              => new Shipment($this->whenLoaded('shipment')),
            'carrier'               => new Carrier($this->whenLoaded('carrier')),
            'service'               => new Service($this->whenLoaded('service')),
            'claim'                 => new Claim($this->whenLoaded('claim')),
            'user'                  => new User($this->whenLoaded('user'))
        ];
    }
}
