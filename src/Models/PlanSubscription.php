<?php

namespace ExpertShipping\Spl\Models;

use App\Enum\PlanSubscriptionStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanSubscription extends Model
{
    use HasFactory;

    protected $table = 'plan_subscriptions';

    protected $guarded = [];

    protected $casts = [
        'status'            => PlanSubscriptionStatusEnum::class,
    ];

    protected $dates = [
        "start_date",
        "end_date"
    ];

    public function plan_package(): BelongsTo
    {
        return $this->belongsTo(PlanPackage::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
