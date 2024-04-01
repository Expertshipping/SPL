<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use App\LocalInvoice;

class CarrierInvoiceShipment extends Pivot
{
    use HasFactory;
    protected $table = "carrier_invoice_shipments";

    protected $casts = [
        'surcharges' => 'array',
        'audited_dimensions' => 'array',
    ];

    public function carrierInvoice()
    {
        return $this->belongsTo(CarrierInvoice::class);
    }

    public function carrierInvoiceSurchargedInvoice()
    {
        return $this->hasOne(LocalInvoice::class, 'id', 'surcharge_invoice_id');
    }
}
