<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Agent extends User
{
    use HasRoles;
    use HasFactory;

    protected $table = 'users';
    protected $guard_name = 'web';

    public function localInvoices()
    {
        return $this->hasMany(LocalInvoice::class, 'user_id', 'id')
            ->orderBy('id', 'desc');
    }

    public function ipAddresses()
    {
        return $this->hasMany(IpAddress::class, 'user_id', 'id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'user_id', 'id');
    }

    public function shipmentRetail()
    {
        return $this->hasMany(ShipmentRetail::class, 'user_id', 'id');
    }

    public function invoiceRetail()
    {
        return $this->hasMany(InvoiceRetail::class, 'user_id', 'id')
            ->orderBy('id', 'desc');
    }

    public function workingShifts()
    {
        return $this->hasMany(WorkingShift::class, 'user_id', 'id')
            ->orderBy('id', 'desc');
    }
}
