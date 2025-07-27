<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformCountry extends Model
{
    use HasFactory;

    public function domains()
    {
        return $this->belongsToMany(PlatformDomain::class, 'platform_country_domain', 'platform_country_id', 'platform_domain_id');
    }

    public function getWeightUnitAttribute()
    {
        return $this->mesurement_system === 'imperial' ? 'lb' : 'kg';
    }
}
