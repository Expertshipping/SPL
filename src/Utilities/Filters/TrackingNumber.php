<?php

namespace ExpertShipping\Spl\Utilities\Filters;

use ExpertShipping\Spl\Models\CarrierInvoice;
use ExpertShipping\Spl\Models\Shipment;
use ExpertShipping\Spl\Utilities\FilterContract;

class TrackingNumber implements FilterContract
{
    public static function apply($query, $value)
    {
        if(get_class($query->getModel()) === Shipment::class){
            return $query->where('tracking_number', 'like', "%{$value}%");
        }

        if(get_class($query->getModel()) === CarrierInvoice::class){
            return $query->whereHas('trackings', function ($query) use ($value) {
                $query->where('tracking_number', 'like', "%{$value}%");
            });
        }

        if(get_class($query->getModel()) === Invoice::class){
            return $query->whereHas('details', function ($query) use ($value) {
                $query->whereHasMorph('invoiceable', [Shipment::class], function ($query) use ($value) {
                    $query->where('tracking_number', 'like', "%{$value}%");
                });
            });
        }

        return $query->whereHas('shipment', function ($query) use ($value) {
            $query->where('tracking_number', 'like', "%{$value}%");
        });
    }
}
