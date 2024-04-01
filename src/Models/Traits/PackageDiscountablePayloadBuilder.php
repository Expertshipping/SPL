<?php

namespace ExpertShipping\Spl\Models\Traits;

trait PackageDiscountablePayloadBuilder
{
    use PackageCrossJoinable;

    public function buildSchema($requestDisacounts, $requestServiceIds, $serviceField = 'service_id', $fields = [], $dollarPercentage = null)
    {
        $discounts = collect();

        if (is_array($dollarPercentage) && count($dollarPercentage) > 0) {
            collect($requestDisacounts)->filter(function ($discount) {
                return !is_null($discount['us']) && !is_null($discount['canada']) && !is_null($discount['world']);
            })->each(function ($discount) use ($discounts) {
                $discounts->push(['discount' => $discount]);
            });
        } else {
            collect($requestDisacounts)->filter(function ($discount) {
                return !is_null($discount);
            })->each(function ($discount) use ($discounts) {
                $discounts->push(['discount' => $discount]);
            });
        }

        $discountTypes = collect();
        collect($dollarPercentage)->filter(function ($item) {
            return !!$item;
        })->each(function ($item) use ($discountTypes) {
            $discountTypes->push(['type' => $item]);
        });

        $services = collect();
        collect($requestServiceIds)->filter(function ($service) {
            return !!$service;
        })->each(function ($service) use ($services, $serviceField) {
            $services->push([$serviceField => $service]);
        });

        return $this->crossJoinSoft($services, $discounts, $serviceField, $fields, $discountTypes);
    }
}
