<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyPartnerAccount extends Pivot
{
    use HasFactory;

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function partnerAccount()
    {
        return $this->belongsTo(PartnerAccount::class);
    }

    public function getIncrementing()
    {
        return true;
    }
}
