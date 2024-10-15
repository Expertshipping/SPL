<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Models\Traits\PackageDiscountablePayloadBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountPackageDetail extends Model
{
    use PackageDiscountablePayloadBuilder;

    use HasFactory;
    protected $fillable = [
        'name',
        'discount_service_id',
        'discount_package_id',
        'discount',
        'discount_type',
    ];

    protected $casts = [
        'discount' => 'array'
    ];

    function discountPackage()
    {
        return $this->belongsTo(DiscountPackage::class);
    }

    public function discountService()
    {
        return $this->belongsTo(DiscountService::class, 'discount_service_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function companyDiscounts()
    {
        return $this->hasMany(CompanyDiscount::class, 'discount_package_detail_id');
    }

    protected static function booted()
    {
        // static::created(function ($discountPackageDetail) {
        //     ProcessPackageDetailCreated::dispatch($discountPackageDetail);
        // });

        // static::updated(function ($discountPackageDetail) {
        //     $discountPackageDetail->companyDiscounts()->update([
        //         'discount'=> $discountPackageDetail->discount
        //     ]);
        // });

        // static::deleting(function ($discountPackageDetail) {
        //     $discountPackageDetail->companyDiscounts()->delete();
        // });
    }
    public function getIndexByWeight($weight, $country, $zone= null)
    {
        $discount = $this->discount[$country] ?? null;

        if($zone && !$discount) {
            $discount = $this->discount[$zone] ?? null;
        }

        if(!$discount){
            $discount = $this->discount['world'] ?? null;
        }

        if(!$discount){
            return false;
        }

        if (array_key_first($discount) === 'all') {
            return 'all';
        } else {
            foreach ($discount as $key => $value) {
                $explode = explode('-', $key);
                $from = $explode[0] ?? 0;
                $to = $explode[1] ?? 0;

                if ($weight >= $from && $weight < $to) {
                    return $key;
                }
            }
            return false;
        }
    }
}
