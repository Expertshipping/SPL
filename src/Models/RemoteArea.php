<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemoteArea extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function carrier() {
        return $this->belongsTo(Carrier::class);
    }
}
