<?php

namespace ExpertShipping\Spl\Services;

use ExpertShipping\Spl\Models\RemoteArea;

class RemoteAreaDelivery
{
    public static function check(string $country, string $postalCode)
    {
        return RemoteArea::query()
            ->where('country', $country)
            ->where('postal_code', $postalCode)
            ->get()
            ->map(fn($r) => $r->carrier->slug ?? 'ALL')
            ->unique()
            ->values();

    }
}
