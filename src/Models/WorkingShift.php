<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class WorkingShift extends Model
{
    const STATUS_PENDING = 'PENDING';
    const STATUS_ACTIVE = 'ACTIVE';

    protected $table = 'working_shifts';

    protected $guarded = [];

    protected $casts = [
        'days'          => 'array',
        'start_on'      => 'date',
        'end_on'        => 'date',
        'update_form'   => 'array',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
