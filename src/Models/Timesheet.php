<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'manager_id',
        'user_id',
        'company_id',
        'scheduled_start_date',
        'scheduled_end_date',
        'user_proposed_start_date',
        'user_proposed_end_date',
        'valide',
        'state',
        'comment',
        'published'
    ];

    protected $casts = [
        'valide' => 'boolean',
        'published' => 'boolean',
        'scheduled_start_date' => 'datetime',
        'scheduled_end_date' => 'datetime',
        'user_proposed_start_date' => 'datetime',
        'user_proposed_end_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function updateLogs()
    {
        return $this->hasMany(TimesheetUpdateLog::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function updateLogsCollection()
    {
        return $this->updateLogs->map(function ($update) {
            return [
                'id' => $update->id,
                'manager' => $update->user->name,
                'action' => $update->action,
            ];
        });
    }
}
