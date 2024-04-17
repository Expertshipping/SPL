<?php

namespace ExpertShipping\Spl\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class Pickup extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $pickup = parent::toArray($request);
        $pickup['pickup_date'] = $this->pickup_date->format('Y-m-d');
        $pickup['created_at'] = $this->created_at->format('d/m/Y H:i');
        $pickup['created_by'] = $this->user->name;

        if($pickup['carrier']['slug']==='dhl' && substr($pickup['pickup_number'], 0, 2 )!=="AM"){
            $number = str_pad($pickup['pickup_number'], 6, '0', STR_PAD_LEFT);
            $date = Carbon::createFromFormat("Y-m-d", $pickup['pickup_date'])->format("ymd");
            $pickup['pickup_number'] = "AME{$date}{$number}";
        }
        return $pickup;
    }
}
