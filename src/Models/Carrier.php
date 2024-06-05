<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Carrier extends Model implements HasMedia
{
    use InteractsWithMedia;

    public $fillable = [
        'name',
        'slug',
        'key',
        'account_number',
        'username',
        'password',
        'meter_number',
        'paperless',
        'volumetric_weight_airway_factor',
        'volumetric_weight_ground_factor',
        'tracking_link',

        'carrier_logo',
        'carrier_color',
        'is_ltl',
    ];

    public $casts = [
        'is_ltl' => 'boolean',
    ];

    protected $appends = [
        'image_url'
    ];

    protected $morphClass = 'Carrier';

    public function user()
    {
        return $this->belongsToMany(User::class)->withPivot('rabais')->withTimestamps();
    }

    public function pickups()
    {
        return $this->hasMany(Pickup::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public function scopeLtl($query)
    {
        return $query->where('is_ltl', true);
    }

    public function scopeNotLtl($query)
    {
        return $query->where('is_ltl', false);
    }

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('carriers-logo');
    }


    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    public function ShippingSurcharges()
    {
        return $this->hasMany(ShippingSurcharge::class);
    }

    public function getLogoAttribute()
    {
        $carriersLogo = [
            'dhl' => "/images/logo_transporteur/icon-dhl.svg",
            'fedex' => "/images/logo_transporteur/icon-fedex.svg",
            'purolator' => "/images/logo_transporteur/icon-puro.svg",
            'ups' => "/images/logo_transporteur/icon-ups.svg",
            'canada-post' => "/images/logo_transporteur/icon-canada.svg",
            'canada' => "/images/logo_transporteur/icon-canada.svg",
            'canpar' => "/images/logo_transporteur/icon-canpar.svg",
            'usps' => "/images/logo_transporteur/icon-usps.svg",
            'aramex' => "/images/logo_transporteur/icon-aramex.svg",
            'loomis' => "/images/logo_transporteur/loomis.png",
        ];

        return $carriersLogo[$this->slug] ?? '/images/logo_transporteur/icon-transporteur.svg';
    }

    public function shipmentTrackingStatusCodes()
    {
        return $this->hasMany(ShipmentTrackingStatusCode::class);
    }

    public function platformCountries()
    {
        return $this->belongsToMany(PlatformCountry::class)
            ->withPivot([
                'carrier_id',
                'platform_country_id',
                'residential',
                'saturday_delivery',
                'signature_on_delivery',
                'transit_time_source',
                'weight_limit',
                'full_rate_source',
                'discount_rate_source',
                'pickup_api_or_email',
                'pickup_email_address',
                'pickup_email_content',
                'has_fixed_ship_from_location',
                'ship_from_addr1',
                'ship_from_addr2',
                'ship_from_addr3',
                'ship_from_city',
                'ship_from_state',
                'ship_from_zip_code',
                'ship_from_country',
                'support_po_box',
                'active_for_inventory',
                'claim_email',
                'claim_language',
                'reseller_marge_details',
                'has_ground_service',
                'special_handling_price',
            ])
            ->using(CarrierPlatformCountry::class);
    }

    public function getActivePlatformCountryAttribute()
    {
        return $this->platformCountries()
            ->wherePivot('platform_country_id', request()->platformCountry->id)
            ->first();
    }


}
