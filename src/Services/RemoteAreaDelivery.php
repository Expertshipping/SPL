<?php

namespace ExpertShipping\Spl\Services;

use ExpertShipping\Spl\Models\RemoteArea;
use Illuminate\Database\Eloquent\Builder;

class RemoteAreaDelivery
{
    public static function check(string $country, string $postalCode, string $city, string $carrierSlug)
    {

        return RemoteArea::query()
            ->where('country', $country)
            ->where(function (Builder $query) use ($postalCode, $city) {
                $query->where('postal_code', $postalCode)
                    ->orWhere('postal_code', $city);
            })
            ->where(function (Builder $query) use ($carrierSlug) {
                $query->where('carriers', 'LIKE', '%' . $carrierSlug . '%')
                    ->orWhere('carriers', 'ALL');
            })
            ->get()
            ->unique()
            ->values();

    }
}
