<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierAccountUsers extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function userable()
    {
        return $this->morphTo();
    }
}
