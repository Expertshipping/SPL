<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralPayout extends Model
{
    use HasFactory;
    const ACTIVE = 'Active';
    const INACTIVE = 'Inactive';

    protected $guarded = [];
}
