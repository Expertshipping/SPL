<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @SWG\Definition(
 *      definition="Address",
 *      required={
 *          "id",
 *          "uuid",
 *          "name",
 *      },
 *      @SWG\Property(property="id", type="integer", description="the shipping unique system id"),
 *      @SWG\Property(property="uuid", type="uuid", description="the shipping uuid"),
 * )
 */
class Restriction extends JsonResource
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
            'name_fr' => $this->name_fr,
            'description' => optional(optional($this->countries->first())->pivot)->description,
            'description_fr' => optional(optional($this->countries->first())->pivot)->description_fr,
            'type' => optional(optional($this->countries->first())->pivot)->type,
            'countries' => $this->countries->pluck('code')->toArray(),
            'company_id' => $this->company_id,
        ];
    }
}
