<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Product extends JsonResource
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
            'order' => $this->order,
            'name' => $this->name,
            'price' => $this->price,
            'category_product_id' => $this->category_product_id,
            'category' => new CategoryProduct($this->whenLoaded('category')),
            'taxable' => $this->taxable,
            'user_id' => $this->user_id,
            'stockable' => $this->stockable,
            'minimum_required' => $this->minimum_required,
            'inventory_status' => $this->inventory_status,
            'on_hand_inventory' => $this->getInventory()?$this->on_hand_inventory:'NA',
            'consommable' => $this->consommable,
            'variable' => $this->variable,
            'tracking_number' => $this->tracking_number,
            'image' => $this->resource->getFirstMediaUrl('products-images'),
            'managed_stock' => $this->managed_stock,
            'hide_from_pos' => $this->hide_from_pos,
            'name_origin' => $this->getTranslations('name'),
        ];
    }
}
