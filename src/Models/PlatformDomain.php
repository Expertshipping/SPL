<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformDomain extends Model
{
    use HasFactory;

    public function getWeightUnitAttribute()
    {
        return $this->platformCountry->measurement_system === 'imperial' ? 'LB' : 'KG';
    }

    public function countries()
    {
        return $this->belongsToMany(PlatformCountry::class, 'platform_country_domain', 'platform_domain_id', 'platform_country_id');
    }
}
