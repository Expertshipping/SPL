<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Manifest extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = [
        'created_at_formated', 'shipments_count', 'carrier_name'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function getShipmentsCountAttribute()
    {
        return $this->shipments()->count();
    }

    public function getCreatedAtFormatedAttribute()
    {
        return $this->created_at->format("d/m/Y");
    }

    public function getCarrierNameAttribute()
    {
        return $this->carrier->name;
    }
}
