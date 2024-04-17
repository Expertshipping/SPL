<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @SWG\Definition(
 *      definition="Package",
 *      required={
 *          "id",
 *          "uuid",
 *          "name",
 *      },
 *      @SWG\Property(property="id", type="integer", description="the shipping unique system id"),
 *      @SWG\Property(property="uuid", type="uuid", description="the package uuid"),
 * )
 */
class PackageResource extends JsonResource
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
            'uuid' => $this->uuid,
            'shipment' => new Shipment($this->whenLoaded('shipment')),
            'tracking_number' => $this->tracking_number,
            'packaging_type' => $this->packaging_type,
            'signature_type' => $this->signature_type,
            'insured_currency' => $this->insured_currency,
            'package_meta' => $this->meta_data,
            'total_weight' => $this->total_weight,
            'total_value' => $this->total_value,
            'quantity' => $this->quantity,
            'length_unit' => $this->length_unit,
            'weight_unit' => $this->weight_unit,
            'envelope_weight' => $this->envelope_weight,
        ];
    }
}
