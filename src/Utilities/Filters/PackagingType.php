<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;

class PackagingType implements FilterContract
{
    public static function apply($query, $value)
    {
        return $query->whereHas('package', function ($query) use ($value) {
            $query->where('packaging_type', $value);
        });
    }
}
