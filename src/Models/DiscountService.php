<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;

use function foo\func;

class DiscountService extends Model
{
    use DiscountablePayloadBuilder;

    protected $fillable = ['service_id', 'discount'];

    public function particularsDiscount()
    {
        return $this->hasOne(ParticularDiscount::class);
    }

    public function companiesDiscounts()
    {
        return $this->hasMany(CompanyDiscount::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
