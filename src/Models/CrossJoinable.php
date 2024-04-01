<?php


namespace ExpertShipping\Spl\Models;


trait CrossJoinable
{
    public function crossJoinSoft($services, $discounts, $serviceField = 'service_id', $fields = [])
    {
        $array = [];

        $services->each(function ($service, $topKey) use ($discounts, &$array, $serviceField, $fields) {

            $discounts->each(function ($discount, $bottomKey) use ($service, &$array, $topKey, $serviceField, $fields) {
                if ($topKey === $bottomKey) {
                    $array[$topKey] = [
                        $serviceField => $service[$serviceField],
                        'discount' => $discount['discount'],
                    ];

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
