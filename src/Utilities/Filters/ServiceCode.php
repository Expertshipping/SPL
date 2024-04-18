<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;

class ServiceCode implements FilterContract
{
    public static function apply($query, $value)
    {
        return $query->where('service_code', $value);
    }
}
