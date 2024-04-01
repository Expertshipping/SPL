<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetUpdateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'timesheet_id', 'action'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
