<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientExperienceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'client_experience_id', 'token', 'response', 'reviewable_id', 'reviewable_type', 'company_id', 'review'
    ];

    protected $casts = [
        'response' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clientExperience()
    {
        return $this->belongsTo(ClientExperience::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function reviewable()
    {
        return $this->morphTo();
    }
}
