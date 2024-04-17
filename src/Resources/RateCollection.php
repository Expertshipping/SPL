<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RateCollection extends ResourceCollection
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
        return $this->collection->transform(function ($rate) {
            return (new RateResource($rate));
        });
    }

    public function with($request)
    {
        $visibility = ['both' => false, 'full_rate' => false, 'discount_rate' => true];
        $user = auth()->user()->loadMissing('company');
        if($user->hasCompany()) {
            $visibility = $user->rateVisibility();
        }
        return [
            'rate_visibility' => $visibility,
        ];
    }
}
