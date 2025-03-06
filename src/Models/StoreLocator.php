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

    public function googleBusiness()
    {
        return $this->belongsTo(GoogleBusiness::class, 'google_business_id');
    }

    public static function getServicesAvailable() {
        return [
            [
                'name' => __('Service Shipping'),
                'description' => __('Offer a wide range of national and international shipping services'),
                'color' => '#20C4F4',
                'icon' => 'fa fa-plane-up',
            ],
            [
                'name' => __('Box Posts'),
                'description' => __('Forwarding subscriptions and services'),
                'color' => '#FF8C00',
                'icon' => 'fa fa-box',
            ],
            [
                'name' => __('Insurance'),
                'description' => __('Offered insurance for deposits and shipments'),
                'color' => '#172B4D',
                'icon' => 'fa fa-shield-halved',
            ],
            [
                'name' => __('Packaging services and supplies'),
                'description' => __('Specialized packaging and supplies, custom packaging'),
                'color' => '#5D60EC',
                'icon' => 'fa fa-tape',
            ],
            [
                'name' => __('Collection and deposit services (PUDO)'),
                'description' => __('Online order collection and free return services for clients'),
                'color' => '#CC7429',
                'icon' => 'fa fa-dolly',
            ],
            [
                'name' => __('Printing and photocopy services'),
                'description' => __('Professional and personal printing solutions'),
                'color' => '#0C8',
                'icon' => 'fa fa-print',
            ]
        ];
    }
}
