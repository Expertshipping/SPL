<?php

namespace ExpertShipping\Spl\Resources;

use ExpertShipping\Spl\Models\Insurance;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class DropOff extends JsonResource
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
        $insurance = Insurance::query()
            ->where('tracking_number', $this->tracking_number)
            ->first();

        return [
            'uuid' => $this->uuid,
            'carrier' => $this->whenLoaded('carrier'),
            'tracking_number' => $this->tracking_number,
            'store' => $this->store->name,
            'agent' => $this->agent->name ?? 'N/A',
            'date' => is_null($this->created_at) ? 'N/A' : $this->created_at->format('d/m/Y H:i'),
            'with_insurance' => $insurance ? 'Yes' : 'No',
            'insurance_value' => optional($insurance)->declared_value,
            'invoice_id' => $this->invoice_id,
            'phone_number' => $this->phone_number,
            'signature_name' => $this->signature_name,
            'email' => $this->email,
            'origin' => $this->origin,
            'tracking_link' => $this->trackingLink($this->tracking_number, optional($this->carrier)->tracking_link)
        ];
    }
}
