<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CarrierCompanyDropoffPrice extends Pivot
{
    use HasFactory;

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
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
