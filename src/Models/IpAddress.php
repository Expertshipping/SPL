<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpAddress extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'allowed' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
