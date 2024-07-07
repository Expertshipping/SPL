<?php

namespace ExpertShipping\Spl\Models\Retail;

use ExpertShipping\Spl\Models\Retail\AgentWarning;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warning extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'require_tracking_number',
    ];

    protected $casts = [
        'require_tracking_number' => 'boolean',
    ];

    public function agentWarnings()
    {
        return $this->hasMany(AgentWarning::class);
    }
}
