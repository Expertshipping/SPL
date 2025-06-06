<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'status',
        'status_code',
        'description',
        'time'
    ];

    public function shipment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
