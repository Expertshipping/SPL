<?php

namespace ExpertShipping\Spl\Models\Retail;

use ExpertShipping\Spl\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AgentWarning extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'user_id',
        'warning_id',
        'comment',
        'created_by',
        'date',
        'time',
        'status',
        'tracking_number',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warning()
    {
        return $this->belongsTo(Warning::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeCurrent(){
        return $this->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted(){
        return $this->where('status', self::STATUS_COMPLETED);
    }
}
