<?php

namespace ExpertShipping\Spl\Services;

use App\Jobs\GetShipmentCostRate;
use ExpertShipping\Spl\Models\Insurance;
use ExpertShipping\Spl\Models\Shipment;
use Illuminate\Database\Eloquent\Model;

class GetCostService
{
    public static function getCost(Model $model)
    {
        if(in_array(get_class($model), ['App\Shipment', Shipment::class])) {
            if(!$model->cost_rate){
                dispatch_sync(new GetShipmentCostRate($model));
                $model->refresh();
            }

            return $model->cost_rate;
        }

        if(in_array(get_class($model), ['App\Insurance', Insurance::class])) {
            return $model->charge;
        }

        return 0;
    }
}
