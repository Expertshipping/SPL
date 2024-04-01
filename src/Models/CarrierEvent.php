<?php

namespace ExpertShipping\Spl\Models;

use App\Services\Purolator\PurolatorEventsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CarrierEvent extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    const PUROLATOR_DROPOFF_PRICE = 1.00;
    const PUROLATOR_HOLD_FOR_PICKUP_PRICE = 2.00;

    const DDHL_OK_PRICE = 1.50;
    const DDHL_SA_PRICE = 0.75;
    const DDHL_CC_PRICE = 0.75;

    protected $guarded = [];

    protected $casts = [
        'meta_data' => 'array',
        'dropoff_for_late_dropoff_event_sent' => 'boolean'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function getPurolatorCategoryNameAttribute()
    {
        $name = '';

        if (isset($this->meta_data['category']) && $this->meta_data['category'] === 'update') {
            return PurolatorEventsService::getUpdatesName($this->meta_data['update']);
        }

        if (isset($this->meta_data['category']) && $this->meta_data['category'] !== 'update') {
            return PurolatorEventsService::getCategorieName($this->meta_data['category']);
        }

        return $name;
    }

    public function events()
    {
        return $this->hasMany(CarrierEvent::class, 'parent_event_id');
    }

    public function getPurolatorEventCodeAttribute()
    {

        if (isset($this->meta_data['category']) && $this->meta_data['category'] === 'update') {
            return $this->meta_data['update'];
        }

        if (isset($this->meta_data['category']) && $this->meta_data['category'] !== 'update') {
            return $this->meta_data['category'];
        }

        return null;
    }


    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            try {
                // Purolator Events
                if (
                    $model->carrier->slug === 'purolator' &&
                    isset($model->meta_data['code']) &&
                    strlen($model->meta_data['code']) > 34 &&
                    str_contains($model->meta_data['code'], '|') &&
                    str_contains($model->meta_data['code'], '~')
                ) {

                    $data = PurolatorEventsService::getMetaData($model->meta_data['code']);
                    $metaData = $model->meta_data ?? [];
                    $model->meta_data = array_merge($metaData, $data);
                }
            } catch (\Throwable $th) {
                Log::alert("Bug when adding Purolator meta from scan => " . $th->getMessage());
            }
        });
    }
}
