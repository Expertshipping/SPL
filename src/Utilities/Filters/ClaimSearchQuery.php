<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;

class ClaimSearchQuery implements FilterContract
{
    public static function apply($query, $value)
    {
        return $query->where(function($q) use ($value){
            $q->whereHas('claimable', function($q) use ($value){
                $q->where('tracking_number', 'like', '%'.$value.'%')
                    ->orWhereHas('invoiceDetail', function($q) use ($value){
                        $q->where('invoice_id', 'like', '%'.$value.'%');
                    });
            })
            ->orWhere('reason', 'like', '%'.$value.'%');
        });
    }
}
