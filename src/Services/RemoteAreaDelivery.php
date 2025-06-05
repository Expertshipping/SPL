<?php

namespace ExpertShipping\Spl\Services;

use ExpertShipping\Spl\Models\RemoteArea;
use Illuminate\Database\Eloquent\Builder;

class RemoteAreaDelivery
{
    public static function check(string $country, string $postalCode, string $city)
    {
        return RemoteArea::query()
            ->where('country', $country)
            ->where(function (Builder $query) use ($postalCode, $city) {
                $query->where('postal_code', $postalCode)
                    ->orWhere('postal_code', $city);
            })
            ->get()
            ->map(fn($r) => $r->carrier->slug ?? 'ALL')
            ->unique()
            ->values();

    }
}
