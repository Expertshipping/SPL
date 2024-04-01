<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpersonateLog extends Model
{
    use HasFactory;

    public function isExpired()
    {
        return $this->created_at->diffInSeconds(now()) > 5;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
