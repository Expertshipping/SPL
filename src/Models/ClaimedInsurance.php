<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimedInsurance extends Claim
{
    use HasFactory;

    protected $table = "claims";
}
