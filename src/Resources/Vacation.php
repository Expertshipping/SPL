<?php

namespace ExpertShipping\Spl\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class Vacation extends JsonResource
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
            'uuid'              => $this->uuid,
            'currency'          => $this->currency,
            'from'              => $this->from->format('Y-m-d'),
            'to'                => $this->to->format('Y-m-d'),
            'user_id'           => $this->user_id,
            'user'              => $this->whenLoaded('user'),
            'comments'          => $this->whenLoaded('comments', new CommentCollection($this->comments)),
            'approved'          => $this->approved,
            'declined'          => $this->declined,
            'description'       => $this->description,
            'created_at'        => $this->created_at,
        ];
    }
}
