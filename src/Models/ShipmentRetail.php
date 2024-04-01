<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentRetail extends Shipment
{
    use HasFactory;

    protected $table = 'shipments';

    public function package()
    {
        return $this->hasOne(Package::class, 'shipment_id', 'id');
    }

    public function invoice()
    {
        return $this->hasOne(LocalInvoice::class, 'shipment_id');
    }
}
