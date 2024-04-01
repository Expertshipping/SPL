<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyTarget extends Model
{
    use HasFactory;

    protected $casts = [
        'details' => 'array'
    ];

    public function store()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
