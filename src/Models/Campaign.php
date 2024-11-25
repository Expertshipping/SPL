<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'sent' => 'boolean',
        'send_date' => 'datetime',
        'channels' => 'array',
    ];

    public function sentCoupons()
    {
        return $this->hasMany(SentCoupon::class);
    }

    public function sentCouponsInvoiced()
    {
        return $this->sentCoupons()->whereNotNull('invoice_id');
    }

    public function getLeads(){
        $leads = collect();
        if($this->recipient === 'Dop-off'){
            $leads = DropOff::query()
                ->when($this->period, fn($q) => $q->whereBetween('created_at', [$this->send_date->copy()->subMonths($this->period), $this->send_date]))
                ->when(count($this->channels) === 1, function ($q) {
                    if (in_array('phone', $this->channels)) {
                        $q->whereNotNull('phone_number')->distinct('phone_number');
                    } elseif (in_array('email', $this->channels)) {
                        $q->whereNotNull('email')->distinct('email');
                    }
                })
                ->when(count($this->channels) === 2, fn($q) =>
                    $q->where(function ($q) {
                        $q->whereNotNull('phone_number')
                          ->orWhereNotNull('email');
                    })->distinct(['phone_number', 'email'])
                );
        }

        if($this->recipient === 'Shipments'){
            $leads = Lead::whereHas('invoice', function($query){
                $query->whereHas('details', function($query){
                    $query->whereHasMorph('invoiceable', [Shipment::class]);
                });
            })
                ->when($this->period, fn($q) => $q->whereBetween('created_at', [$this->send_date->copy()->subMonths($this->period), $this->send_date]))
                ->when($this->channels, fn($q) => $q->whereIn('type', $this->channels))
                ->distinct('value');
        }

        if($this->recipient === 'Sales without shipments'){
            $leads = Lead::whereHas('invoice', function($query){
                $query->whereHas('details', function($query){
                    $query->whereHasMorph('invoiceable', [Product::class], function($query){
                        $query->where('products.name', 'NOT LIKE', 'Drop-Off%');
                    });
                });
            })
                ->when($this->period, fn($q) => $q->whereBetween('created_at', [$this->send_date->copy()->subMonths($this->period), $this->send_date]))
                ->when($this->channels, fn($q) => $q->whereIn('type', $this->channels))
                ->distinct('value');
        }

        return $leads;
    }
}
