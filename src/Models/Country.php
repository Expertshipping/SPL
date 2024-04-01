<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function zones()
    {
        return $this->belongsToMany(Zone::class)
            ->withPivot(['transit_time'])
            ->withTimestamps();
    }
}
