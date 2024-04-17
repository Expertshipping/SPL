<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AgentWarning extends JsonResource
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
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user'),
            'warning_id' => $this->warning_id,
            'warning' => $this->whenLoaded('warning'),
            'comment' => $this->comment,
            'created_by' => $this->whenLoaded('createdBy'),
            'created_at' => $this->created_at->format('d/m/Y H:i A'),
            'date' => $this->date->format('Y-m-d'),
            'time' => $this->time->format('H:i'),
            'proof' => $this->getFirstMediaUrl('warning-proof-images'),
            'tracking_number' => $this->tracking_number,
        ];
    }
}
