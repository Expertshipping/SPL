<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Company
{
    use HasFactory;

    protected $table = 'companies';

    public function discounts()
    {
        return $this->hasMany(CompanyDiscount::class, 'company_id', 'id');
    }

    public function taxNumbers()
    {
        return $this->hasMany(TaxNumber::class, 'company_id', 'id');
    }

    public function localInvoices()
    {
        return $this->hasMany(LocalInvoice::class, 'company_id', 'id')
            ->orderBy('id', 'desc');
    }

    public function margeCategories()
    {
        return $this->hasMany(ResellerCompanyCategoryMarge::class, 'company_id', 'id')
            ->orderBy('id', 'desc');
    }
}
