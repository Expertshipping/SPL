<?php

namespace ExpertShipping\Spl\Services;

use ExpertShipping\Spl\Models\RemoteArea;

class RemoteAreaDelivery
{

    public function check($country, $zipCode)
    {
        return RemoteArea::query()
            ->where('country', $country)
            ->where('postal_code', $zipCode)
            ->get()
            ->map(fn($r) => [
                'carrier' => $r->carrier->slug ?? 'ALL',
            ])
            ->toArray();
    }
}
