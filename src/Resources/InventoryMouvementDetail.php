<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryMouvementDetail extends JsonResource
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
            'inventory_mouvement_id' => $this->inventory_mouvement_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'product' => new Product($this->whenLoaded('product')),
        ];
    }
}
