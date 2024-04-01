<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentTrackingStatusCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'carrier_id',
        'code',
        'type',
    ];

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }
}
