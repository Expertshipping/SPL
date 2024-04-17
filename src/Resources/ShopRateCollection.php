<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ShopRateCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function toArray($request)
    {
        return [
            'rates' => $this->collection->transform(function ($rate) {
                            return (new ShopRateResource($rate));
                        })->toArray()
        ];
    }

}
