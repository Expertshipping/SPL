<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;

class Type implements FilterContract
{
    public static function apply($query, $value)
    {
        return $query->where('type', $value);
    }
}
