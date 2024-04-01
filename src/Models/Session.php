<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    protected $table = 'shopify_sessions';


    public function getShopUrlAttribute()
    {
        return str('https://')->append($this->shop)->value();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
