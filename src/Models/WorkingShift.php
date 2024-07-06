<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class WorkingShift extends Model
{

    protected $guarded = [];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

}
