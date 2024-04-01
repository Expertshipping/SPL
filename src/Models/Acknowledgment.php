<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acknowledgment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'acknowledgmentable_id',
        'acknowledgmentable_type',
        'acknowledged_at',
        'admin_notification_id',
        'verify_after',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'verify_after' => 'datetime',
    ];

    public function acknowledgmentable()
    {
        return $this->morphTo();
    }

    public function adminNotification()
    {
        return $this->belongsTo(AdminNotification::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
