<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentCoupon extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function campaign() {
        return $this->belongsTo(Campaign::class);
    }

    public function invoice() {
        return $this->hasOne(LocalInvoice::class, 'invoice_id');
    }

    public function coupon() {
        return $this->belongsTo(Coupon::class);
    }
}
