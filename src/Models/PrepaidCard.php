<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrepaidCard extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function generateUniqueCode()
    {
        $code = rand(111111, 999999);
        if (self::where('code', $code)->exists()) {
            return self::generateUniqueCode();
        }

        return $code;
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function store()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
