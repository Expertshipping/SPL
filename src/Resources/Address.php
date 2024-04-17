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
class Address extends JsonResource
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
            'full_name' => $this->full_name,
            'company' => $this->company,
            'phone' =>$this->phone,
            'addr1' => $this->addr1,
            'addr2' => $this->addr2,
            'addr3' => $this->addr3,
            'city' => $this->city,
            'email' => $this->email,
            'province' => $this->state,
            'country' => $this->country,
            'code' => $this->code,
            'note' => $this->discretionary_notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'model' => class_basename($this),
        ];
    }
}
