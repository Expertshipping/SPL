<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;

class ParticularDiscount extends Model
{
    use DiscountablePayloadBuilder;
    protected $guarded = [];

    public function discountService()
    {
        return $this->belongsTo(DiscountService::class, 'discount_service_id');
    }
}
