<?php

namespace ExpertShipping\Spl\Resources;

use ExpertShipping\Spl\Models\Shipment as AppShipment;
use Illuminate\Http\Resources\Json\JsonResource;

class Claim extends JsonResource
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
            'uuid'=> $this->uuid,
            'created_at'=> $this->created_at->format("d/m/Y H:i"),
            'damaged_value'=> $this->damaged_value,
            'description'=> $this->description,
            'details'=> $this->details,
            'goods_value'=> $this->goods_value,
            'reason'=> $this->reason,
            'shipment_cost'=> $this->shipment_cost,
            'status'=> $this->status,
            'type'=> $this->type,
            'updated_at' => $this->updated_at,
            'user'=> $this->user,
            'invoice'=> $this->invoice,
            'messages'=> $this->whenLoaded('messages', ClaimMessage::collection($this->messages)),
            'medias'=> $this->medias,
            'claimable'=> $this->claimable,
            'meta_data'=> $this->meta_data,
            'shipment' => $this->when($this->claimable_type == AppShipment::class, new Shipment($this->claimable->loadMissing('carrier'))),
            'expiring_at' => $this->status === 'saved' ? $this->created_at->addDays(7)->format('d/m/Y H:i') : null,
            'submited_at' => $this->submited_at,
            'current_step_url' => $this->current_step_url,
            'messages_count' => $this->messages_count,
        ];
    }
}
