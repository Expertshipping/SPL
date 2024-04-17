<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryMouvement extends JsonResource
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
            'from' => $this->from,
            'to' => $this->to,
            'details' => new InventoryMouvementDetailCollection($this->inventoryMouvementDetails->load('product')),
            'type_mouvement' => $this->type_mouvement,
        ];
    }
}
