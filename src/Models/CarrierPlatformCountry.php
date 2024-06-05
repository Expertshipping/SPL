<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CarrierPlatformCountry extends Pivot
{
    use HasFactory;

    public $incrementing = true;
    protected $guarded = [];

    public $casts = [
        'has_fixed_ship_from_location' => 'boolean',
        'support_po_box' => 'boolean',
        'active_for_inventory' => 'boolean',
        'reseller_marge_details' => 'array',
        'has_ground_service' => 'boolean',
    ];

    public function scopeActiveForInventory($query)
    {
        return $query->where('active_for_inventory', true);
    }
}
