<?php

namespace ExpertShipping\Spl\Models\Traits;

trait HasTrackingLink
{
    public function trackingLink($tracking_number, $tracking_link)
    {
        if (!$tracking_number || !$tracking_link) {
            return null;
        }
        return str_replace(
            '{tracking_number}',
            $tracking_number,
            $tracking_link
        );
    }
}
