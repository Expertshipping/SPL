<?php

namespace App\Models;

use ExpertShipping\Spl\Models\PlatformCountry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PlatformCountryDomain extends Pivot
{
    protected $table = 'platform_country_domain';

    protected $fillable = [
        'platform_country_id',
        'plaform_domain_id',
    ];

    public function platformCountry()
    {
        return $this->belongsTo(PlatformCountry::class, 'platform_country_id');
    }
}
