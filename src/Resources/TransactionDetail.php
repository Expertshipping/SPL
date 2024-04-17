<?php

namespace ExpertShipping\Spl\Resources;

use ExpertShipping\Spl\Models\Insurance;
use ExpertShipping\Spl\Models\Product;
use ExpertShipping\Spl\Models\Shipment;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionDetail extends JsonResource
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
            'name' => $this->product_name,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'taxes' => $this->taxes,
            'total_taxes' => round($this->total_taxes, 2),
            'total' => round($this->total, 2),
            'total_discount' => round($this->total_discount, 2),
            'discount_value' => round($this->discount_value, 2),
            'discount_name' => $this->discount_name,
        ];
    }
}
