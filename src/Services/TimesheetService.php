<?php


namespace ExpertShipping\Spl\Services;

use ExpertShipping\Spl\Models\Timesheet;
use ExpertShipping\Spl\Models\User;

class TimesheetService
{
    public function getTotalHours($from, $TimesheetServiceto, $companyId=null){
        $timesheets = Timesheet::query()
        ->when($companyId, function($q) use($companyId){
            return $q->where('company_id', $companyId);
        })
        ->whereDate('scheduled_start_date','>=', $from)
        ->whereDate('scheduled_end_date','<=', $to)
        ->select('scheduled_start_date', 'scheduled_end_date')
        ->get();

        return $timesheets->sum(function ($timesheet) {
            return $timesheet->scheduled_start_date->diffInHours($timesheet->scheduled_end_date);
        });
    }

    public function getTotalHoursByMonth($month, $year, $companyId=null){
        $timesheets = Timesheet::query()
        ->when($companyId, function($q) use($companyId){
            return $q->where('company_id', $companyId);
        })
        ->whereMonth('scheduled_start_date', $month)
        ->whereYear('scheduled_start_date', $year)
        ->select('scheduled_start_date', 'scheduled_end_date')
        ->get();

        return $timesheets->sum(function ($timesheet) {
            return $timesheet->scheduled_start_date->diffInHours($timesheet->scheduled_end_date);
        });
    }

    public function getTotalHoursForUser($from, $to, $companyId = null, $userId = null){
        $timesheets = Timesheet::query()
                        ->when($companyId, function($q) use($companyId){
                            return $q->where('company_id', $companyId);
                        })
                        ->when($userId, function($q) use($userId){
                            return $q->where('user_id', $userId);
                        })
                        ->whereDate('scheduled_start_date','>=', $from)
                        ->whereDate('scheduled_end_date','<=', $to)
                        ->select('scheduled_start_date', 'scheduled_end_date')
                        ->get();

        return $timesheets->sum(function ($timesheet) {
            return $timesheet->scheduled_start_date->diffInHours($timesheet->scheduled_end_date);
        });
    }

    public function getTotalHoursForUserByMonth($month, $year, $companyId, $userId){
        $timesheets = Timesheet::query()
                        ->where('company_id', $companyId)
                        ->where('user_id', $userId)
                        ->whereMonth('scheduled_start_date', $month)
                        ->whereYear('scheduled_start_date', $year)
                        ->select('scheduled_start_date', 'scheduled_end_date')
                        ->get();

        return $timesheets->sum(function ($timesheet) {
            return $timesheet->scheduled_start_date->diffInHours($timesheet->scheduled_end_date);
        });
    }

    public function fortyHoursExceededForUser($from, $to, $user_id, $newHours = 0){
        $user = User::find($user_id);
        if($user->agent_type === 'self-employed'){
            return false;
        }

        $timesheets = Timesheet::where('user_id', $user_id)
                        ->whereDate('scheduled_start_date','>=', $from)
                        ->whereDate('scheduled_end_date','<=', $to)
                        ->select('scheduled_start_date', 'scheduled_end_date', 'user_id')
                        ->get();

        $countHours = $timesheets->sum(function ($timesheet) {
            return $timesheet
            ->scheduled_start_date
            ->diffInHours($timesheet->scheduled_end_date);
        });

        return ($countHours + $newHours) > 40;
    }

    public static function getTotalHoursForUserAllTime($companyId = null, $userId = null){
        $timesheets = Timesheet::query()
                        ->when($companyId, function($q) use($companyId){
                            return $q->where('company_id', $companyId);
                        })
                        ->when($userId, function($q) use($userId){
                            return $q->where('user_id', $userId);
                        })
                        ->select('scheduled_start_date', 'scheduled_end_date')
                        ->get();

        return $timesheets->sum(function ($timesheet) {
            if(!is_null($timesheet->scheduled_start_date) && !is_null($timesheet->scheduled_end_date)) {
                return $timesheet->scheduled_start_date->diffInHours($timesheet->scheduled_end_date);
            } else {
                return 0;
            }
        });
    }
}
