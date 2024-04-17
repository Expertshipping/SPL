<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Timesheet extends JsonResource
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
            'name' => $this->user?"{$this->user->name}":'',
            'comment' => $this->comment,
            'company_id' => $this->company_id,
            'created_at' => $this->created_at,
            'manager_id' => $this->manager_id,
            'state' => $this->state,
            'user_id' => $this->user_id,
            'valide' => $this->valide,
            'published' => $this->published,
            'end' => $this->scheduled_end_date->format("Y-m-d H:i"),
            'start' => $this->scheduled_start_date->format("Y-m-d H:i"),

            'start_hour' => $this->scheduled_start_date->format("H:i"),
            'end_hour' => $this->scheduled_end_date->format("H:i"),

            'start_day' => $this->scheduled_start_date->format("Y-m-d"),
            'end_day' => $this->scheduled_end_date->format("Y-m-d"),

            'user_start' => $this->user_proposed_start_date ? $this->user_proposed_start_date->format("Y-m-d H:i") : null,
            'user_end' => $this->user_proposed_end_date ? $this->user_proposed_end_date->format("Y-m-d H:i") : null,
            'color' => $this->getColor(),
            'user_color' => $this->user->color??'',
            'user' => $this->whenLoaded('user'),
            'company' => $this->whenLoaded('company'),
        ];
    }

    private function getColor(){
        // valide
        if($this->valide){
            return "#459fe3";
        }

        // Not validated yet
        if($this->scheduled_start_date < now()){
            return "#e3b445";
        }

        // Scheduled
        if($this->scheduled_end_date > now()){
            return "#3ece7f";
        }
    }
}
