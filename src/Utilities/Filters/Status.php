<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Utilities\FilterContract;

class Status implements FilterContract
{
    public static function apply($query, $value)
    {
        if(get_class($query->getModel()) === 'App\Invoice'){
            if($value === 'paid'){
                return $query->whereNotNull('paid_at');
            }

            if($value === 'unpaid'){
                return $query->whereNull('paid_at');
            }

            if($value === 'refunded'){
                return $query->whereNotNull('refunded_at');
            }
        }

        if(get_class($query->getModel()) === 'App\Shipment'){
            return $query->where('type', $value);
        }

        if(get_class($query->getModel()) === 'App\Insurance'){
            return $query->whereHas('shipment', function ($query) use ($value) {
                $query->where('type', $value);
            });
        }
        
        return $query->where('status', $value);
    }
}
