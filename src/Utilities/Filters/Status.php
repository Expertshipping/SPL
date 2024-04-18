<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;

class Status implements FilterContract
{
    public static function apply($query, $value)
    {
        return $query->where('status', $value);
    }
}
