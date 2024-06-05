<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformCountry extends Model
{
    use HasFactory;

    public function domains()
    {
        return $this->hasMany(PlatformDomain::class);
    }
}
