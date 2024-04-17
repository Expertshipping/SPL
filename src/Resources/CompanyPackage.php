<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyPackage extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'weight' => $this->weight,
            'insured_value' => $this->insured_value,
            'type' => $this->type,
            'pallet_freight_class' => $this->pallet_freight_class,
            'pallet_nmfc_code' => $this->pallet_nmfc_code,
            'pallet_type' => $this->pallet_type,
            'pallet_pieces' => $this->pallet_pieces,
            'pallet_description' => $this->pallet_description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
