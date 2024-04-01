<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangedPriceCode extends Model
{
    use HasFactory;

    public $fillable = [
        'agent_id',
        'manager_id',
        'code',
        'burned',
    ];
}
