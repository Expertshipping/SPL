<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;

class InvoiceSearchQuery implements FilterContract
{
    public static function apply($query, $value)
    {
        return $query->where('id', 'like', '%' . $value . '%');
    }
}
