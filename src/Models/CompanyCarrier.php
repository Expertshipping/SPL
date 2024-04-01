<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyCarrier extends Model
{
    use HasFactory;

    protected $table = 'company_carrier';

    protected $guarded = [];

    protected $casts = [
        'options' =>  'array',
    ];

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carrierAccountUsers()
    {
        return $this->morphMany(CarrierAccountUsers::class, 'userable');
    }
}
