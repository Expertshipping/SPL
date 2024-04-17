<?php

namespace ExpertShipping\Spl\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class AgentAvailability extends JsonResource
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
            'id'                => $this->id,
            'day'              => $this->day->format('Y-m-d'),
            'from'              => $this->from->format('H:i'),
            'to'                => $this->to->format('H:i'),
            'user_id'           => $this->user_id,
            'user'              => $this->whenLoaded('user'),
            'manager'           => $this->whenLoaded('manager'),
            'created_at'        => $this->created_at,
        ];
    }
}
