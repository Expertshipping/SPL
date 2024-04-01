<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $guarded = [];

    public function mediable()
    {
        return $this->morphTo();
    }
}
