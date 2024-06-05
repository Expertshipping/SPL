<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformDomain extends Model
{
    use HasFactory;

    public function platformCountry()
    {
        return $this->belongsTo(PlatformCountry::class);
    }

    public function getWeightUnitAttribute()
    {
        return $this->platformCountry->measurement_system === 'imperial' ? 'LB' : 'KG';
    }
}
