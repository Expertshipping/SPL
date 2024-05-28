<?php

namespace ExpertShipping\Spl\Models;

use App\Integration;
use ExpertShipping\Spl\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmazonSession extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }
}
