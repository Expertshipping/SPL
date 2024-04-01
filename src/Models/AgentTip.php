<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\LocalInvoice;

class AgentTip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'invoice_id',
        'tip_amount',
    ];

    protected $appends = [
        'tip_amount_without_fees'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
        return $this->belongsTo(LocalInvoice::class);
    }

    public function getTipAmountWithoutFeesAttribute()
    {
        return round($this->tip_amount - $this->tip_amount * 0.02, 2);
    }
}
