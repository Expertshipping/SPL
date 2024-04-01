<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Models\LargePackageShippingRate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class)
            ->withPivot(['transit_time'])
            ->withTimestamps();
    }

    public function largePackageShippingRate()
    {
        return $this->morphOne(LargePackageShippingRate::class, 'rateable');
    }
}
