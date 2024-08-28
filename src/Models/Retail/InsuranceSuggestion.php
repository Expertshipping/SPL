<?php

namespace ExpertShipping\Spl\Models\Retail;

use ExpertShipping\Spl\Models\Company;
use ExpertShipping\Spl\Models\DropOff;
use ExpertShipping\Spl\Models\Insurance;
use ExpertShipping\Spl\Models\Shipment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'shipment_id',
        'drop_off_id',
        'payment_token',
        'status',
        'payment_response',
    ];

    protected $casts = [
        'payment_response' => 'array',
    ];

    public function shipment(){
        return $this->belongsTo(Shipment::class);
    }

    public function dropOff(){
        return $this->belongsTo(DropOff::class);
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function insurances(){
        return $this->hasMany(Insurance::class);
    }

}
