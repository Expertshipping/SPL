<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Models\Traits\HasUniqueCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundCode extends Model
{
    use HasFactory;
    use HasUniqueCode;

    protected $table = 'refund_codes';
    protected $fillable = [
        'code',
        'comment',
        'manager_id',
        'user_id',
        'company_id',
        'invoice_id',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function invoice()
    {
        return $this->belongsTo(LocalInvoice::class, 'invoice_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
