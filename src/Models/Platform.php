<?php

namespace ExpertShipping\Spl\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    use HasFactory;

    protected $casts = [
        'access_token' => 'array',
        'access_token.expire_date' => 'datetime',
    ];

    public function checkAccessToken()
    {
        return $this->access_token && isset($this->access_token['expire_date']) && Carbon::parse($this->access_token['expire_date'])->greaterThanOrEqualTo(now());
    }

    public function discountPackage()
    {
        return $this->belongsTo(DiscountPackage::class);
    }

    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }
}
