<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;

class CarrierId implements FilterContract
{
    public static function apply($query, $value)
    {
        return $query->where('carrier_id', $value);
    }
}
