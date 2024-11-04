<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'date'          => 'datetime:Y-m-d',
        'date_from'     => 'datetime:Y-m-d',
        'date_to'       => 'datetime:Y-m-d',
        'is_yearly'     => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
