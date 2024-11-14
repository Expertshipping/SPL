<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportContent extends Model
{
    use HasFactory;

    public $guarded = [];

    public function supportCategory()
    {
        return $this->belongsTo(SupportCategory::class);
    }
}
