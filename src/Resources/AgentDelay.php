<?php

namespace ExpertShipping\Spl\Resources;

use ExpertShipping\Spl\Models\Timesheet;
use ExpertShipping\Spl\Models\TimesheetLog;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentDelay extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $sessions = TimesheetLog::where('user_id', $this->user_id)
        ->where('company_id', $this->company_id)
        ->whereDate('check_in', $this->scheduled_start_date->format('Y-m-d'))
        ->get()->map(function($session){
            return [
                'id' => $session->id,
                'start_hour' => $session?$session->check_in->format('H:i'):'-',
                'end_hour' => $session && $session->check_out ?$session->check_out->format('H:i'):'-',
            ];
        });

        return [
            'id' => $this->id,
            'date' => $this->scheduled_start_date->format('d/m/Y'),
            'timesheet_start_hour' => $this->scheduled_start_date->format('H:i'),
            'agent' => $this->user?"{$this->user->name}":'',
            'store' => $this->company?"{$this->company->name}":'',
            'sessions' => $sessions,
            // 'delay' => intdiv($this->delay, 60).':'. ($this->delay % 60),
            'delay' => $this->delay
        ];
    }

}
