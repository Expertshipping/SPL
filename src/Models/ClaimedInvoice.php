<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimedInvoice extends Claim
{
    use HasFactory;

    protected $table = 'claims';

    public function messages()
    {
        return $this->hasMany(Message::class, 'claim_id');
    }
}
