<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;

class Ref implements FilterContract
{
    public static function apply($query, $value)
    {
        return $query->where('ref', $value);
    }
}
