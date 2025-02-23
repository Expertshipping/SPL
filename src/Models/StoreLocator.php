<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreLocator extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    public static function getServicesAvailable() {
        return [
            [
                'name' => 'UPS Ground',
                'description' => 'UPS Ground',
                'color' => '#ff0000',
                'icon' => 'fa fa-truck',
            ],
            [
                'name' => 'UPS Next Day Air',
                'description' => 'UPS Next Day Air',
                'color' => '#ff0000',
                'icon' => 'fa fa-truck',
            ],
            [
                'name' => 'UPS 2nd Day Air',
                'description' => 'UPS 2nd Day Air',
                'color' => '#ff0000',
                'icon' => 'fa fa-truck',
            ],
            [
                'name' => 'UPS 3 Day Select',
                'description' => 'UPS 3 Day Select',
                'color' => '#ff0000',
                'icon' => 'fa fa-truck',
            ],
            [
                'name' => 'UPS Next Day Air Saver',
                'description' => 'UPS Next Day Air Saver',
                'color' => '#ff0000',
                'icon' => 'fa fa-truck',
            ],
            [
                'name' => 'UPS Next Day Air Early A.M.',
                'description' => 'UPS Next Day Air Early A.M.',
                'color' => '#ff0000',
                'icon' => 'fa fa-truck',
            ]
        ];
    }
}
