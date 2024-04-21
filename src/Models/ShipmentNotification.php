<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'notification',
        'admin_user_id',
        'client_user_id',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function clientUser()
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }
}
