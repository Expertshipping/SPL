<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessToBusinessUserRole extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function businessToBusinessRole()
    {
        return $this->belongsTo(BusinessToBusinessRole::class, 'business_to_business_role_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIncrementing()
    {
        return true;
    }
}
