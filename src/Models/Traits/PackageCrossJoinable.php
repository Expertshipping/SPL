<?php


namespace ExpertShipping\Spl\Models\Traits;


trait PackageCrossJoinable
{
    public function crossJoinSoft($services, $discounts, $serviceField = 'service_id', $fields = [], $discountTypes = null)
    {
        $array = [];

        $services->each(function ($service, $topKey) use ($discounts, &$array, $serviceField, $fields, $discountTypes) {

            $discounts->each(function ($discount, $bottomKey) use ($service, &$array, $topKey, $serviceField, $fields, $discountTypes) {
                if ($topKey === $bottomKey) {
                    if ($discountTypes->count() > 0) {
                        $array[$topKey] = [
                            $serviceField => $service[$serviceField],
                            'discount' => $discount['discount'],
                            'type' => $discountTypes[$bottomKey]['type'],
                        ];
                    } else {
                        $array[$topKey] = [
                            $serviceField => $service[$serviceField],
                            'discount' => $discount['discount'],
                        ];
                    }

                    if (count($fields) > 0) {
                        foreach ($fields as $field => $value) {
                            $array[$topKey][$field] = $value;
                        }
                    }
                }
            });
        });

        return $array;
    }
}
