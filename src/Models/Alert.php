<?php

namespace ExpertShipping\Spl\Models;

use App\Enums\AlertStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_dismissible'    => 'boolean',
        'status'            => AlertStatusEnum::class,
    ];
}
