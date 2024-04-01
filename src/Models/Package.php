<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Cashier;
use Ramsey\Uuid\Uuid;

class Package extends Model
{
    protected $fillable = [
        'shipment_id',
        'tracking_number',
        'packaging_type',
        'signature_type',
        'insured_currency',
        'length_unit',
        'weight_unit',
        'meta_data',
        'envelope_weight',
        'pallet_options',
    ];

    protected $casts = [
        'meta_data' => 'array',
        'pallet_options' => 'array',
    ];

    protected $append = ['total_weigth', 'total_value', 'quantity'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function getMetaDataAttribute($package)
    {
        $package =  json_decode($package);

        if (is_array($package)) {
            return collect($package)->map(function ($package) {
                $package->value = (float) $package->insured_value / 100;
                unset($package->insured_value);
                return $package;
            })->toArray();
        }

        if (property_exists($package, 'insured_value')) {
            $package->value = (float) $package->insured_value / 100;
            unset($package->insured_value);
        }

        return [$package];
    }

    public function getTotalWeightAttribute()
    {
        return collect($this->meta_data)->sum('weight');
    }

    public function getQuantityAttribute()
    {
        if (in_array($this->packaging_type, ['box', 'pack'])) {
            return count($this->meta_data);
        }

        return collect($this->meta_data)->first()->quantity;
    }

    public function getTotalValueAttribute()
    {
        return $this->formatAmount((float) collect($this->meta_data)->sum('value') * 100);
    }

    public function shipment()
    {
        return $this->belongsTo('App\Shipment');
    }

    /**
     * Format the given amount into a displayable currency.
     *
     * @param  int  $amount
     * @param $currency
     *
     * @return string
     */
    protected function formatAmount($amount, $currency = null)
    {
        return Cashier::formatAmount($amount, $currency ?: config('cashier.currency'));
    }

    public function getInsuredValueAttribute()
    {
        return collect($this->meta_data)->sum('value');
    }
}
