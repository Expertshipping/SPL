<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemoteArea extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'remote_courier_code' => 'json',
    ];
}
