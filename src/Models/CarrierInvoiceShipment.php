<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use ExpertShipping\Spl\Models\LocalInvoice;

class CarrierInvoiceShipment extends Pivot
{
    use HasFactory;
    protected $table = "carrier_invoice_shipments";

    protected $guarded = [];

    protected $casts = [
        'surcharges' => 'array',
        'audited_dimensions' => 'array',
        'taxes' => 'array',
        'rate_details' => 'array',
    ];

    public function carrierInvoice(){
        return $this->belongsTo(CarrierInvoice::class);
    }

    public function carrierInvoiceSurchargedInvoice()
    {
        return $this->hasOne(LocalInvoice::class, 'id', 'surcharge_invoice_id');
    }
}
