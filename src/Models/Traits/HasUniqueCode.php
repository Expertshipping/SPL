<?php

namespace ExpertShipping\Spl\Models\Traits;

trait HasUniqueCode
{
    public static function generateNewCode()
    {
        $freecode = rand(11111, 99999);
        while (static::where('code', $freecode)->exists()) {
            $freecode = rand(11111, 99999);
        }

        return $freecode;
    }
}
