<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMouvement extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function inventoryMouvementDetails()
    {
        return $this->hasMany(InventoryMouvementDetail::class);
    }
}
