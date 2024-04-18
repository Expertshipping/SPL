<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;

class UserId implements FilterContract
{
    public static function apply($query, $value)
    {
        return $query->where('user_id', $value);
    }
}
