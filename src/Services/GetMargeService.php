<?php

namespace ExpertShipping\Spl\Services;

use ExpertShipping\Spl\Models\Company;
use ExpertShipping\Spl\Models\Insurance;
use ExpertShipping\Spl\Models\Product;
use ExpertShipping\Spl\Models\Shipment;
use Illuminate\Database\Eloquent\Model;

class GetMargeService
{
    public static function getMarge(Model $model, $revenue, Company $company)
    {
        if(in_array(get_class($model), ['App\Shipment', Shipment::class])) {
            return $company->reseller_insurance_shipment_marge[$revenue]['shipment'] ?? 0;
        }

        if(in_array(get_class($model), ['App\Insurance', Insurance::class])) {
            return $company->reseller_insurance_shipment_marge[$revenue]['insurance'] ?? 0;
        }

        if(in_array(get_class($model), ['App\Product', Product::class])) {
            $marge = $company->resellerCompanyCategoryMarges()
                ->whereHas('category', function($query) use ($model) {
                    $query->where(function ($query) use ($model) {
                        $query->where('id', $model->category_id)
                            ->orWhere('parent_id', $model->category_id);
                    });
                })
                ->first();

            if($marge) {
                return $marge->marge;
            }
        }

        return 0;
    }
}
