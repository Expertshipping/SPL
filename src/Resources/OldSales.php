<?php

namespace ExpertShipping\Spl\Resources;

use ExpertShipping\Spl\Models\Refund;
use Illuminate\Http\Resources\Json\JsonResource;

class OldSales extends JsonResource
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
            'id' => $this->resource->id,
            'invoiceable' => $this->resource->invoiceable,
            'claim' => $this->resource->claim,
            'paymentGateway' => $this->resource->paymentGateway,
            'details' => $this->resource->details->load('invoiceable'),
            'total' => $this->total,
            'created_at' => $this->created_at,
            'refunded' => $this->refunded(),
            'corrected' => $this->corrected(),
            'metadata' => $this->metadata,
            'same_day' => $this->sameDay(),
            'leads' => $this->whenLoaded('leads')
        ];
    }

    protected function refunded(){
        return !$this->resource->details->filter(fn($detail)=> is_null($detail->refund_id))->count()>0;
    }

    protected function corrected(){
        return $this->resource->details->first() && $this->resource->details->first()->correction_refund_id;
    }

    protected function sameDay(){
        return $this->resource->created_at->isToday();
    }
}
