<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function rateable()
    {
        return $this->morphTo();
    }
}
