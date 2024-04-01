<?php

namespace ExpertShipping\Spl\Models;

use App\Enum\PlanStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded;

    protected $casts = [
        'status'            => PlanStatusEnum::class,
    ];

    public function packages(): HasMany
    {
        return $this->hasMany(PlanPackage::class);
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(PlanFeature::class, 'plan_option', 'plan_id', 'plan_feature_id')->withTimestamps();
    }
}
