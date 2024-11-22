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
                    'start' => $start->copy(),
                    'end' => $end->copy(),
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

    public static function getPayPeriodFromRequest(): array
    {
        if(request('pay_range_from') && request('pay_range_to') && request('agent_type') === 'self-employed'){
            $start = Carbon::parse(request()->get('pay_range_from'))->format('Y-m-d');
            $end = Carbon::parse(request()->get('pay_range_to'))->format('Y-m-d');
        }else{
            $payPeriods = PayPeriodsService::getPayPeriods(date('Y'));
            $currentPeriod = $payPeriods[request()->get('pay_period', 0)];
            $start = $currentPeriod['start']->format('Y-m-d');
            $end = $currentPeriod['end']->format('Y-m-d');
        }

        $paymentPeriod = $start.' - '.$end;

        return [
            'start' => $start,
            'end' => $end,
            'paymentPeriod' => $paymentPeriod,
        ];
    }

}
