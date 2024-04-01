<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_id',
        'shop_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'marketing_opt_in',
        'default_address',
        'verified_email',
        'shop_created_at',
        'shop_updated_at',
        'currency',
    ];

    protected $casts = [
        'default_address' => 'array',
    ];

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }
}
