<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonerisLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'data',
        'user_id',
        'company_id',
        'invoice_id'
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invoice()
    {
        return $this->belongsTo(LocalInvoice::class, 'invoice_id');
    }
}
