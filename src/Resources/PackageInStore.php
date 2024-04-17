<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PackageInStore extends JsonResource
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
            'tracking_number' => $this->tracking_number,
            'code' => $this->meta_data['code'],
            'name' => $this->signatory,
            'category' => $this->purolator_category_name,
            'store' => $this->company->name,
            'location_id' => $this->company->purolator_loc_id,
            'address' => $this->company->addr1 . " " . $this->company->zip_code,
            'agent' => isset($this->meta_data['auto']) && $this->meta_data['auto'] ? '-' : $this->user->name,
            'date' => $this->created_at->format("d/m/Y H:i"),
            'signature_origin' => $this->signature_origin,
            'events' => new PackageInStoreCollection($this->whenLoaded('events')),
            'arpc_agent_auto' => $this->meta_data['auto'] ?? null,
        ];
    }
}
