<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentAvailability extends Model
{
    use HasFactory;

    public $fillable = [
        'manager_id',
        'user_id',
        'day',
        'from',
        'to',
    ];

    protected $casts = [
        'day' => 'datetime:Y-m-d',
        'from' => 'datetime:H:i',
        'to' => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
