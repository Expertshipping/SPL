<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDiscount extends Model
{
    use DiscountablePayloadBuilder;

    protected $guarded = [];

    // protected $fillable = [
    //     'company_id',
    //     'discount_service_id',
    //     'discount',
    //     'discount_package_detail_id',
    //     'discount_package_id',
    // ];

    protected $casts = [
        'discount' => 'array'
    ];

    public function discountService()
    {
        return $this->belongsTo(DiscountService::class, 'discount_service_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function store()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function discountPackage()
    {
        return $this->belongsTo(DiscountPackage::class);
    }
}
