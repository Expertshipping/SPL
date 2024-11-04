<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ResellerCompanyCategoryMarge extends Model
{
    protected $table = 'reseller_company_category_marge';

    protected $guarded = [];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(CategoryProduct::class);
    }
}
