<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RefundCode extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'company_name' => $this->whenLoaded('company', optional($this->company)->name),
            'manager_name' => $this->whenLoaded('manager', optional($this->manager)->name),
            'user_name' => $this->whenLoaded('user', optional($this->user)->name),
            'invoice_id' => $this->invoice_id,
            'code' => $this->code,
            'date' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
