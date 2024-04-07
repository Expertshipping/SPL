<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;

class UnblockedRequest extends Model
{
    protected $table = 'unblocked_requests';

    protected $guarded = [];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
