<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyDiscountPackage extends Pivot
{
    use HasFactory;

    public function discountPackage()
    {
        return $this->belongsTo(DiscountPackage::class, 'discount_package_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getIncrementing()
    {
        return true;
    }
}
