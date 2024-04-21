<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;

class InvoiceStatus implements FilterContract
{
    public static function apply($query, $value)
    {
        if($value == 'paid') {
            return $query->whereNotNull('paid_at');
        }

        if($value == 'unpaid') {
            return $query->whereNull('paid_at');
        }

        if($value == 'overdue') {
            return $query->where('created_at', '<', now()->subDays(30))
                ->whereNull('paid_at');
        }

        if($value == 'upcoming') {
            return $query->where('created_at', '>', now()->subDays(30))
                ->whereNull('paid_at');
        }

        if($value == 'urgent') {
            return $query->where('created_at', '<', now()->subDays(30))
                ->whereNull('paid_at');
        }

        return $query;
    }
}
