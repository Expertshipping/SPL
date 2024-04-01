<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FulfillmentLocation extends Model
{
    use HasFactory;
    protected $fillable = [
        "shop_id",
        "user_id",
        "name",
        "address1",
        "address2",
        "address3",
        "city",
        "zip",
        "phone",
        "country",
        "province",
        "legacy",
        "active",
        "integration_id",
    ];

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }
}
