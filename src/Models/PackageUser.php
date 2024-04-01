<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'length',
        'width',
        'height',
        'weight',
        'insured_value',
        'length_unit',
        'weight_unit',
        'user_id'
    ];

    public function getVolumeAttribute()
    {
        if ($this->width && $this->height && $this->length) {
            return $this->width * $this->height * $this->length;
        }
        return false;
    }
}
