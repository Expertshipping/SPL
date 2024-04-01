<?php

namespace ExpertShipping\Spl\Models;

use App\Traits\HasUniqueCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreeLabelCode extends Model
{
    use HasFactory;
    use HasUniqueCode;

    protected $guarded = [];


    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
