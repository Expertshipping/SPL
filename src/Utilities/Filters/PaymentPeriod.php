<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;

class PaymentPeriod implements FilterContract
{
    public static function apply($query, $value)
    {
        return $query->where('payment_period', $value);
    }
}
