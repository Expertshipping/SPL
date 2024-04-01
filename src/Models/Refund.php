<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'total',
        'details',
        'reason',
        'company_id',
        'invoiceable_type',
        'invoiceable_id',
        'user_id',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function invoiceDetail()
    {
        return $this->morphOne(InvoiceDetail::class, 'invoiceable');
    }

    public function refundable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
