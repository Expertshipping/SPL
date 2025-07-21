<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountCarrier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'carrier_id',
        'api_credentials',
        'rate_type',
        'rate',
        'label',
        'pickup',
        'name',
        'type',
        'reseller_marge',
        'display_name',
        'platform_country_id',
    ];

    protected $casts = [
        'api_credentials' => 'array',
        'rate' => 'boolean',
        'label' => 'boolean',
        'pickup' => 'boolean',
    ];

    public function carrier()
    {
        return $this->belongsTo(Carrier::class)->where('active', 1);
    }

    public static function accountForCarrier($slug)
    {
        $carrier = Carrier::where('slug', $slug)->first();
        $account = self::query()->where('carrier_id', $carrier->id)->where('label', true)->first();
        return $account->api_credentials;
    }

    public function carrierAccountUsers()
    {
        return $this->morphMany(CarrierAccountUsers::class, 'userable');
    }

    public function platformCountry()
    {
        return $this->belongsTo(PlatformCountry::class);
    }
}
