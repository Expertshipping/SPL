<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    use HasFactory;

    protected $table = 'users';

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user', 'user_id', 'company_id')
            ->using(CompanyUser::class)
            ->withPivot('app_role_id')
            ->withTimestamps();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
