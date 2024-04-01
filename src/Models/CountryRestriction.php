<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CountryRestriction extends Pivot
{
    use HasFactory;

    protected $guarded = [];

    public function restriction()
    {
        return $this->belongsTo(Restriction::class);
    }

    public function getTranslatedTypeAttribute()
    {
        if (app('translator')->getLocale() === 'fr' && $this->type_fr) {
            return  $this->type_fr;
        }

        return $this->type;
    }

    public function getTranslatedDescriptionAttribute()
    {
        if (app('translator')->getLocale() === 'fr' && $this->description_fr) {
            return  $this->description_fr;
        }

        return $this->description;
    }
}
