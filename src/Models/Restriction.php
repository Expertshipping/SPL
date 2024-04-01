<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restriction extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getTranslatedNameAttribute()
    {
        if (app('translator')->getLocale() === 'fr' && $this->name_fr) {
            return  $this->name_fr;
        }

        return $this->name;
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class, 'country_restriction', 'restriction_id', 'to_country_id',)
            ->using(CountryRestriction::class)
            ->withPivot(['description', 'description_fr', 'type', 'type_fr']);
    }
}
