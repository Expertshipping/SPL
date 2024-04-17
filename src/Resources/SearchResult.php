<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class SearchResult extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->transformResource($this->resource);
    }

    public function transformResource($resource){
        $model = get_class($resource);
        if($model == 'App\Shipment') {
            return  new Shipment($resource);
        }
//        switch ($model) {
//            case 'App\User':
//                return [
//                    'model' => 'User',
//                    'uuid' => $resource->uuid,
//                    'full_name' => $resource->full_name,
//                    'phone' => $resource->phone,
//                    'company' => $resource->company,
//                    'addr1' => $resource->addr1,
//                    'addr2' => $resource->addr2,
//                    'addr3' => $resource->addr3,
//                    'city' => $resource->city,
//                    'country' => $resource->country,
//                    'code' => $resource->code,
//                    'email' => $resource->email
//                ];
//
//                case 'App\Shipment':
//                    return  new Shipment($resource);
//
//                case 'App\Address':
//                    return  new Address($resource);
//
//            default:
//                return collect($resource)->put('model', $model);
//        }
    }


}
