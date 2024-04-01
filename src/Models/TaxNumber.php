<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxNumber extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'tax_slug',
        'number',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
