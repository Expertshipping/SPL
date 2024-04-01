<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRefund extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }
}
