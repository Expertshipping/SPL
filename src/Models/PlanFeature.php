<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class PlanFeature extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasTranslations;

    protected $translatable = ['content'];

    protected $guarded;

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_option', 'plan_feature_id', 'plan_id');
    }
}
