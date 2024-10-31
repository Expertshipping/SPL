<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;
use Illuminate\Support\Carbon;

class Date implements FilterContract
{
    public static function apply($query, $value)
    {
        if(str_contains($value, ',')) {
            $value = explode(',', $value);
        }

        if(is_array($value)) {
            $dateFrom = Carbon::create($value[0]);
            $dateTo = Carbon::create($value[1]);
            return $query
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo);
        }

        return $query->whereDate('created_at', $value);
    }
}
