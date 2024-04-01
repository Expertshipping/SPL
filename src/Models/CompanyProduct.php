<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "quantity",
        "weight",
        "unit",
        "description",
        "manufacturing_country",
        "hs_no",
        "value",
        "user_id",
        "company_id",
        'shop_id',
        'sku',
        'barcode',
        'image',
        'integration_id',
        'dimensions'
    ];

    protected $casts = [
        'dimensions' => 'array'
    ];
}
