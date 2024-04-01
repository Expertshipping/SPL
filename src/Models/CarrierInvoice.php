<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierInvoice extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function trackings()
    {
        return $this->belongsToMany(Shipment::class, 'carrier_invoice_shipments')
            ->using(CarrierInvoiceShipment::class)
            ->withPivot([
                'surcharges',
                'status',
                'net_charge',
                'net_surcharge',
                'audited_dimensions',
                'surcharge_invoice_id',
            ]);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}
