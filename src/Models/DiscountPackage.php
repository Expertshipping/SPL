<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountPackage extends Model
{
    use HasFactory;

    protected $casts = [
        'default_b2c' => 'boolean',
        'default_fullrate_retail_reseller' => 'boolean',
        'default_b2b' => 'boolean',
    ];

    function discountPackageDetails()
    {
        return $this->hasMany(DiscountPackageDetail::class);
    }
    public static function boot()
    {
        parent::boot();
        self::deleting(function ($discountPackage) {
            $discountPackage->discountPackageDetails()->delete();
        });
    }
}
