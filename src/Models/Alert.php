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
        'is_accepted'       => 'boolean',
        'status'            => AlertStatusEnum::class,
        'confirmed_users'   => 'array',
        'companies_ids'     => 'array',
        'closed_users'      => 'array',
    ];
}
