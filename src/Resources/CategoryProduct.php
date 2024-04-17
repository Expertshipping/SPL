<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryProduct extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'consommable' =>$this->consommable,
            'company_id' => $this->company_id,
            'parent_id' => $this->parent_id,
            'inventory_report_page_number' => $this->inventory_report_page_number,
            'inventoriable' => $this->inventoriable,
            'image_url' => $this->image_url,
            'products' => Product::collection($this->whenLoaded('products')),
        ];
    }
}
