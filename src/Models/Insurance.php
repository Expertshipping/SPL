<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use ExpertShipping\Spl\Models\LocalInvoice;
use Illuminate\Support\Str;

class Insurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'carrier_id',
        'service_id',
        'tracking_number',
        'ship_date',
        'declared_value',
        'ship_from',
        'ship_to',
        'name',
        'phone',
        'email',
        'transaction_number',
        'price',
        'status',
        'description',
        'claim_id',
        'token',
        'shipment_id',
        'company_id',
        'edit_insurance_id',
        'reseller_charged',
        'charge',
        'from',
        'to',
    ];

    protected $casts = [
        'ship_date' => 'datetime',
        'from' => 'array',
        'to' => 'array',
    ];

    protected $appends = ['link_token'];

    protected $with = ['shipment'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function invoice()
    {
        return $this->morphOne(LocalInvoice::class, 'invoiceable');
    }

    public function claim()
    {
        return $this->morphOne(Claim::class, 'claimable');
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public static function createFromShipment(Shipment $shipment, $tracking_number, $declaredValue)
    {
        $service = Service::where('code', $shipment->service_code)->firstOrfail();
        $insuranceRate = app('insurance')->getRate($declaredValue, $shipment->from_country, $shipment->to_country, $shipment->carrier->slug, $service->code);
        $insuranceCharge = app('insurance')->getRate($declaredValue, $shipment->from_country, $shipment->to_country, $shipment->carrier->slug, $service->code, true);

        if ($shipment->user->account_type === 'retail') {
            $rate = $insuranceRate['rate'] ?? 0;
            $rate1 = floatval(str_replace(",", "", $shipment->rate));
            $rate2 = floatval(str_replace(",", "", $rate));
            $shipment->update([
                'rate' => ($rate1 - $rate2)
            ]);
        }

        return self::create([
            'user_id' => $shipment->user_id,
            'carrier_id' => $shipment->carrier_id,
            'service_id' => $service->id,
            'shipment_id' => $shipment->id,
            'tracking_number' => $tracking_number,
            'ship_date' => $shipment->start_date,
            'declared_value' => $declaredValue,
            'ship_from' => $shipment->from_country,
            'ship_to' => $shipment->to_country,
            'price' => $insuranceRate['rate'] ?? 0,
            'company_id' => $shipment->user->company_id,
            'reseller_charged' => $insuranceRate['charge'] ?? 0,
            'charge' => $insuranceCharge['charge'] ?? 0,
        ]);
    }

    public function InvoiceDetail()
    {
        return $this->morphOne(InvoiceDetail::class, 'invoiceable');
    }
    
    public function receiptDetail()
    {
        return $this->morphOne(InvoiceDetail::class, 'invoiceable')
            ->where('pos', true);
    }

    public function saleDetail()
    {
        return $this->morphOne(InvoiceDetail::class, 'invoiceable')
            ->where('pos', false);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getLinkTokenAttribute()
    {
        if (!$this->token) {
            $this->update([
                'token' => Str::random(40)
            ]);
        }

        return $this->token;
    }

    public function refundable()
    {
        return $this->morphOne(Refund::class, 'refundable');
    }

    public function editedInsurance()
    {
        return $this->belongsTo(Insurance::class, 'edit_insurance_id');
    }

    public function dropOffs()
    {
        return $this->hasMany(dropOff::class);
    }
}
