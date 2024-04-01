<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingSurcharge extends Model
{
    use HasFactory;

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }
}
