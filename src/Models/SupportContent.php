<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Enum\ProfileType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SupportContent extends Model
{
    use HasFactory;

    public $guarded = [];

    public function supportCategory()
    {
        return $this->belongsTo(SupportCategory::class);
    }

    protected $casts = [
        'profiles' => 'array',
    ];

    public function setProfilesAttribute(array $profiles)
    {
        $this->attributes['profiles'] = json_encode(
            array_map(fn($profile) => $profile instanceof ProfileType ? $profile->value : $profile, $profiles)
        );
    }

    public function getProfilesAttribute($value)
    {
        return array_map(fn($profile) => ProfileType::tryFrom($profile), json_decode($value, true) ?? []);
    }

    public function scopeWhereProfile(Builder $query, ProfileType $profileType): Builder
    {
        return $query->whereJsonContains('profiles', $profileType->value);
    }
}
