<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientExperience extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getFivePointScaleAttribute()
    {
        $rate = 0;
        switch ($this->rate) {
            case 3:
                $rate = 5;
                break;
            case 2:
                $rate = 2.5;
                break;
            case 1:
                $rate = 0;
                break;
        }
        return $rate;
    }
}
