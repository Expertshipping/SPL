<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;
use App\LocalInvoice;
use Ramsey\Uuid\Uuid;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Claim extends Model implements HasMedia
{
    use InteractsWithMedia;
    use Traits\Filterable;

    protected $guarded = [];

    protected $casts = [
        'meta_data' => 'array',
        'submited_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
            $model->goods_value = (float) $model->goods_value * 100;
            $model->damaged_value = (float) $model->damaged_value * 100;
        });
    }

    protected function getDamagedValueAttribute($value)
    {
        return (Money::fromCent($value))->inCurrencyAmount();
    }

    protected function getStatusAttribute($value)
    {
        if ($value === 'partially_refunded') {
            return 'Partially refunded';
        }
        return $value;
    }

    protected function getGoodsValueAttribute($value)
    {
        return (Money::fromCent($value))->inCurrencyAmount();
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(LocalInvoice::class);
    }

    public function claimable()
    {
        return $this->morphTo();
    }

    /**
     * Messages relationship.
     *
     * @codeCoverageIgnore
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function medias()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getCurrentStepUrlAttribute()
    {
        if ($this->status === 'saved') {
            if (!isset($this->meta_data['contact_details'])) {
                return '/new-claim/step-one/' . $this->uuid;
            }

            return '/new-claim/step-two/' . $this->uuid;
        }

        return '/claims/details/' . $this->uuid;
    }
}
