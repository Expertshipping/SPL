<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;

class ClaimStatus implements FilterContract
{
    public static function apply($query, $value)
    {
        if($value === 'all') {
            return $query;
        }

        if($value === 'saved') {
            return $query->where('status', 'saved');
        }

        return $query->where('status', $value);
    }
}
