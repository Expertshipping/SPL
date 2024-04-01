<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Integration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'platform_id', 'status', 'store_name', 'store_url', 'store_token', 'checkout', 'shopify_carrier_service_id', 'checkout_api_supported',
        'meta_data', 'webhooks_ids', 'location_id'
    ];

    protected $casts = [
        'meta_data' => 'array',
        'webhooks_ids' => 'array'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->status = 1;
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function fulfillmentLocations()
    {
        return $this->hasMany(FulfillmentLocation::class);
    }

    public function primaryFulfillmentLocation()
    {
        return $this->belongsTo(FulfillmentLocation::class, 'location_id');
    }

    public function products()
    {
        return $this->hasMany(CompanyProduct::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
