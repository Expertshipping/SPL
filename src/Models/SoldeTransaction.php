<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldeTransaction extends Model
{
    use HasFactory;

    const TYPES = [
        'REFUND' => 'refund',
        'ADD_FUNDS' => 'add_funds',
        'PURCHASE' => 'purchase',
    ];

    protected $fillable = [
        'user_id',
        'company_id',
        'soldeable_id',
        'soldeable_type',
        'amount',
        'type',
    ];

    public function soldeable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withDefault();
    }

    public function getDisplayTypeAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getsoldeableDetailsAttribute()
    {
        if ($this->soldeable_type === Shipment::class) {
            return __('Shipment') . ' #' . $this->soldeable->tracking_number;
        }

        return class_basename($this->soldeable_type);
    }
}
