<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;

class Dates implements FilterContract
{
    public static function apply($query, $value)
    {
        if(is_string($value)) {
            $value = explode(',', $value);
        }

        return $query->whereRaw('cast(created_at as date) between ? and ?', [$value[0], $value[1]]);
    }
}
