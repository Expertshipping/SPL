<?php

namespace ExpertShipping\Spl\Services;

use Illuminate\Support\Carbon;

class PayPeriodsService{

    public static function getPayPeriods($year){
        $year = (int) $year;
        $start = Carbon::create(2023, 1, 1);
        $periods = [];
        $index = 0;
        do{
            $index++;
            // end equals to the second next saturday
            $end = $start->copy()->next('Saturday')->next('Saturday');

            if($start->year == $year || $end->year == $year || $end->year == $year - 1){
                $periods[] = [
                    'start' => $start->copy()->startOfDay(),
                    'end' => $end->copy()->endOfDay(),
                    'value' => $index - 1,
                    'label' => $index . " : " . $start->copy()->format('d M Y') . ' - ' . $end->copy()->format('d M Y'),
                ];
            }
            $start = $end->addDay();
        }while($end->year <= $year);

        return $periods;
    }

    public static function getPayPeriodsByDate($year, $date){
        $periods = self::getPayPeriods($year);
        foreach ($periods as $period) {
            // check if between start and end or equals to start or end
            if($date->between($period['start'], $period['end']) || $date->diffInDays($period['start']) == 0 || $date->diffInDays($period['end']) == 0){
                return $period;
            }
        }
        return null;
    }

}
