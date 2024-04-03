<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Enum\PlanPackageStatusEnum;
use ExpertShipping\Spl\Enum\PlanPackageTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlanPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'type'              => PlanPackageTypeEnum::class,
        'status'            => PlanPackageStatusEnum::class,
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function planSubscriptions(): HasMany
    {
        return $this->hasMany(PlanSubscription::class);
    }
}
