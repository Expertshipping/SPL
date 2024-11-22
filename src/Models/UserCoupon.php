<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCoupon extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

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
