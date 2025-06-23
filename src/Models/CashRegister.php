<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashRegister extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function cashRegisterSessions()
    {
        return $this->hasMany(CashRegisterSession::class)->orderBy('created_at', 'desc');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function store()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
