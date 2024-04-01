<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierPickup extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'ground_service' => 'boolean',
        'date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public static function isCompleted($carrierId)
    {
        return self::whereDate('date', today())
            ->where('carrier_id', $carrierId)
            ->where('company_id', auth()->user()->company_id)
            // ->where('ground_service', $carrierId === 1 ? true : false)
            ->exists();
    }
}
