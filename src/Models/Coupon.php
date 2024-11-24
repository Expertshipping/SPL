<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'lead_coupons')->withTimestamps();
    }
}
