<?php


namespace ExpertShipping\Spl\Models;


trait DiscountablePayloadBuilder
{
    use CrossJoinable;

    public function buildSchema($requestDisacounts, $requestServiceIds, $serviceField = 'service_id', $fields = [])
    {
        $discounts = collect();

        collect($requestDisacounts)->filter(function ($discount) {
            return !is_null($discount['us']) && !is_null($discount['canada']) && !is_null($discount['world']);
        })->each(function ($discount) use ($discounts) {
            $discounts->push(['discount' => $discount]);
        });

        $services = collect();
        collect($requestServiceIds)->filter(function ($service) {
            return !!$service;
        })->each(function ($service) use ($services, $serviceField) {
            $services->push([$serviceField => $service]);
        });

        return $this->crossJoinSoft($services, $discounts, $serviceField, $fields);
    }
}
