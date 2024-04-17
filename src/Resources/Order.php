<?php

namespace ExpertShipping\Spl\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class Order extends JsonResource
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
            'created_at'        => $this->created_at,
            'currency'          => $this->currency,
            'customer_name'     => $this->customer_name,
            'integration_id'    => $this->integration_id,
            'integration'       => $this->whenLoaded('integration'),
            'items'             => $this->items,
            'order_created_at'  => $this->order_created_at, // D F Y
            'ship_to'           => $this->ship_to,
            'shipment'          => $this->shipment,
            'shipment_id'       => $this->shipment_id,
            'shop_id'           => $this->shop_id,
            'shop_number'       => $this->shop_number,
            'status'            => $this->status,
            'subtotal_price'    => $this->subtotal_price,
            'total_price'       => $this->total_price,
            'total_tax'         => $this->total_tax,
            'total_weight'      => $this->total_weight,
            'updated_at'        => $this->updated_at,
            'user_id'           => $this->user_id,
            'user'              => $this->whenLoaded('user'),
            'products'          => $this->products,
            'shop_logo'         => $this->integration->platform->logo,
            'adequate_package'  => $this->adequate_package,
        ];
    }
}
