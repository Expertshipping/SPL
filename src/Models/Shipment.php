<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Models\Jobs\CalculateDistanceBetweenStoreAndClientForRetailShipments;
use ExpertShipping\Spl\Models\Models\ReferralPayout;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use ExpertShipping\Spl\Models\Traits\HasTrackingLink;
use Ramsey\Uuid\Uuid;

class Shipment extends Model
{
    use HasTrackingLink;
    const DRAFT = 'draft';
    const CANCELLED = 'cancelled';
    const BASE_PRICE_TYPES = [
        'BasePrice',
        'Base Price',
        'TransportationCharges',
        'Transportation Charges',
        'TotalBaseCharge',
        'base',
        'Freight Charge'
    ];
    const START_DATE_WHEN_COMMENTS_ARE_REQUIRED = '2023-08-07';

    const STATUSES = [
        'in_progress' => 'Ready',
        'cancelled' => 'Cancelled',
        'delivered' => 'Delivered',
        'in_transit' => 'In transit',
        'pickedup' => 'Picked up',
        'exception' => 'Exception',
        'returned' => 'Returned',
    ];

    const FREIGHT_CHARGES = [
        'BasePrice',
        'Base Price',
        'TransportationCharges',
        'TotalBaseCharge',
        'base',
        'Transportation Charges',
        'Freight Charge',
    ];

    const FUEL_CHARGES = [
        'Surcharge-Fuel',
        'fuel',
        'Fuel Surcharge',
        'fuel_surcharge',
        'FUEL SURCHARGE',
        'Fuel charge',
    ];

    const TAXES_CHARGES = [
        'Tax-PSTQST',
        'Tax-HST',
        'Tax-GST',
        'Tax-QST',
        'Tax-PST',
        'HST',
        'GST',
        'QST',
        'PST',
        'TaxCharges-HST',
        'TaxCharges-GST',
        'TaxCharges-QST',
        'TaxCharges-PST',
        'hst',
        'gst',
        'PEHST',
        'pstqst',
        'tax',
        'PSTQST',
    ];

    public $fillable = [
        'user_id',
        'uuid',
        'carrier_id',
        'factured',
        'pickedup',
        'voided',
        'residential',
        'rate',
        'tracking_number',
        'type',
        'rate',
        'from_name',
        'from_company',
        'from_address_1',
        'from_address_2',
        'from_address_3',
        'from_zip_code',
        'from_city',
        'from_country',
        'from_province',
        'from_phone',
        'from_email',

        'to_name',
        'to_company',
        'to_address_1',
        'to_address_2',
        'to_address_3',
        'to_zip_code',
        'to_city',
        'to_country',
        'to_province',
        'to_phone',
        'to_email',
        'terms_of_sale',
        'package_content',
        'document_type',
        'shipping_purpose',
        'b13_number',
        'commodity_description',
        'hs_no',
        'country_of_manufacture',
        'service_code',
        'cpf_cnpj',
        'original_shipment_id',
        'return_label',
        'shipment_carrier_id',
        'start_date',
        'invoiced_at',
        'failed_at',
        'customer_invoice_id',
        'invoice_id',
        'reference_value',
        'carrier_price',
        'description',
        'insurance_transaction_number',
        'insurance_transaction_voided',
        'label_url',

        'label_deleted_at',
        'company_id',
        'cost_rate',
        'cost_retries',

        'accountable_id',
        'accountable_type',
        'manifest_id',
        'rate_details',

        'manifest_charged',

        'carrier_invoice_id',
        'carrier_invoice_surcharges',
        'carrier_invoice_status',
        'carrier_invoice_net_charge',
        'carrier_invoice_net_surcharge',
        'carrier_invoice_audited_dimensions',
        'carrier_invoice_surcharge_invoice_id',

        'signature_on_delivery',
        'saturday_delivery',

        'referral_payout_id',
        'has_issue',
        'issue_description',

        'is_not_found',

        'edit_shipment_id',

        'reseller_charged',
        'cost_rate_details',

        'has_billing_issue',
        'billing_issue_description',
        'referral_payout_id',

        'consumer_id',
        'merged_labels',

        'fedex_tag_confirmation_number',

        'bulk',

        'is_paid',
        'aramex_bulk_id',
        'cost_taxes',

        'distance_details',
        'special_handling',

        'tracking_details',

        'estimated_delivery_date',
        'picked_up_at',
        'delivered_at',
        'freightcom_id'
    ];

    protected $casts = [
        'invoiced' => 'boolean',
        'picked_up' => 'boolean',
        'voided' => 'boolean',
        'residential' => 'boolean',
        'start_date' => 'date',
        'insurance_transaction_voided' => 'boolean',
        'rate_details' => 'array',
        'manifest_charged' => 'boolean',
        'carrier_invoice_surcharges' => 'array',
        'carrier_invoice_audited_dimensions' => 'array',
        'has_issue' => 'boolean',
        'is_not_found' => 'boolean',
        'cost_rate_details' => 'array',
        'has_billing_issue' => 'boolean',
        'bulk' => 'boolean',
        'is_paid' => 'boolean',
        'cost_taxes' => 'array',
        'distance_details' => 'array',
        'special_handling' => 'boolean',

        'tracking_details' => 'array',
        'estimated_delivery_date' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function toSearchableArray()
    {
        $shipment = $this->toArray();

        return  [
            'id' => $shipment['id'],
            'tracking_number' => $shipment['tracking_number'],
            'from_name' => $shipment['from_name'],
            'from_company' => $shipment['from_company'],
            'from_email' => $shipment['from_email'],
            'from_phone' => $shipment['from_phone'],
            'to_name' => $shipment['to_name'],
            'to_company' => $shipment['to_company'],
            'to_email' => $shipment['to_email'],
            'to_phone' => $shipment['to_phone'],
        ];
    }

    public function getRateAttribute($value)
    {
        return (Money::fromCent($value))->inCurrencyAmount();
    }

    public function setRateAttribute($value)
    {
        $this->attributes['rate'] = (Money::fromCurrencyAmount($value))->inCent();
    }
    public function setCarrierPriceAttribute($value)
    {
        $this->attributes['carrier_price'] = (Money::fromCurrencyAmount($value))->inCent();
    }

    public function pickup()
    {
        return $this->belongsTo(Pickup::class)->whereNull('canceled_at');
    }


    public function returnLabel()
    {
        return $this->hasOne(Shipment::class, 'original_shipment_id');
    }

    public function originalLabel()
    {
        return $this->belongsTo(Shipment::class, 'original_shipment_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User')->withTrashed();
    }

    public function package()
    {
        return $this->hasOne(Package::class);
    }

    public function invoice()
    {
        return $this->hasOne(LocalInvoice::class);
    }

    public function invoices()
    {
        return $this->hasMany(LocalInvoice::class, 'shipment_id');
    }


    public function customInvoice()
    {
        return $this->hasOne(CustomerInvoice::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function addPackage(Package $package)
    {
        return $this->package()->save($package);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function scopeWithInvoice($query)
    {
        $query->addSelect([
            'invoice_id' => LocalInvoice::select('id')
                ->whereColumn('shipment_id', 'shipments.id')
                ->latest()
                ->take(1)

        ])->with('invoice');
    }

    public function scopeWithService($query)
    {
        $query->addSelect([
            'service_id' => Service::select('id')
                ->whereColumn('code', 'shipments.service_code')
                ->latest()
                ->take(1)

        ])->with('service');
    }

    public function scopeFilterBySearchTerm($query, $term)
    {
        $query->when($term, function ($query, $term) {
            collect(str_getcsv($term, ' ', '"'))->filter()->each(function ($term) use ($query) {
                $term = $term . '%';
                $query->where(function ($query) use ($term) {
                    $query->where('tracking_number', 'like', $term)
                        ->orWhere('from_name', 'like', "%$term%")
                        ->orWhere('reference_value', 'like', "%$term%")
                        ->orWhere('from_company', 'like', $term)
                        ->orWhere('from_address_1', 'like', $term)
                        ->orWhere('from_address_2', 'like', $term)
                        ->orWhere('from_city', 'like', $term)
                        ->orWhere('from_province', 'like', $term)
                        ->orWhere('from_email', 'like', "%$term%")
                        ->orWhere('from_phone', 'like', "%$term%")

                        ->orWhere('to_name', 'like', "%$term%")
                        ->orWhere('to_company', 'like', "%$term%")
                        ->orWhere('to_address_1', 'like', $term)
                        ->orWhere('to_address_2', 'like', $term)
                        ->orWhere('to_city', 'like', $term)
                        ->orWhere('to_province', 'like', $term)
                        ->orWhere('to_email', 'like', "%$term%")
                        ->orWhere('to_phone', 'like', "%$term%")
                        ->orWhereIn('carrier_id', function ($query) use ($term) {
                            $query->select('id')
                                ->from('carriers')
                                ->where('name', 'like', $term)
                                ->orWhere('slug', 'like', $term);
                        });
                });
            });
        });
    }

    public function clientExperienceDetail()
    {
        return $this->morphOne(ClientExperienceDetail::class, 'reviewable');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function invoiceDetail()
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

    public function insurance()
    {
        return $this->hasOne(Insurance::class);
    }

    public function accountable()
    {
        return $this->morphTo();
    }

    public function insurances()
    {
        return $this->hasMany(Insurance::class);
    }

    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }

    public function carrierInvoice()
    {
        return $this->belongsTo(CarrierInvoice::class);
    }

    public function getColorAttribute()
    {
        if ((float) $this->marge_diff == 0) {
            return 'white';
        }

        if ((float) $this->marge_diff > 0) {
            return 'red';
        }

        return 'green';
    }

    public function carrierInvoiceSurchargedInvoice()
    {
        return $this->hasOne(LocalInvoice::class, 'id', 'carrier_invoice_surcharge_invoice_id');
    }

    public function getMargeDiffAttribute()
    {
        if ($this->cost_rate) {
            $diff = round($this->carrierInvoices->sum('pivot.net_charge') - $this->carrierInvoices->sum('pivot.carrierInvoiceSurchargedInvoice.total') - $this->cost_rate, 2);
        } else {
            $diff = 0;
        }

        return $diff;
    }

    public function referralPayout()
    {
        return $this->belongsTo(ReferralPayout::class);
    }

    public function refundable()
    {
        return $this->morphOne(Refund::class, 'refundable');
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function editedShipment()
    {
        return $this->belongsTo(Shipment::class, 'edit_shipment_id');
    }

    public function getTotalChargedAttribute()
    {
        return collect($this->rate_details)->sum(function ($detail) {
            if (isset($detail['amount'])) {
                return floatval(str_replace(",", "", $detail['amount']));
            }
            return 0;
        });
    }

    public function carrierInvoices()
    {
        return $this->belongsToMany(CarrierInvoice::class, 'carrier_invoice_shipments')
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

    public function consumer()
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    public function aramexBulk()
    {
        return $this->belongsTo(Shipment::class, 'aramex_bulk_id');
    }

    public function aramexBulks()
    {
        return $this->hasMany(Shipment::class, 'aramex_bulk_id');
    }

    public function addressCorrections()
    {
        return $this->hasMany(AddressCorrection::class, 'shipment_id');
    }

    public function surcharges()
    {
        return $this->hasMany(LocalInvoice::class)
            ->whereNotNull('surcharge_details');
    }

    public function calculateDistanceFromStore()
    {
        dispatch(new CalculateDistanceBetweenStoreAndClientForRetailShipments($this));
    }

    public function getToRegionAttribute()
    {
        $countries = Cache::remember('countries', 500, function () {
            return Country::all();
        });

        return optional($countries->where('code', $this->to_country)->first())->region;
    }

    public function getToSubRegionAttribute()
    {
        $countries = Cache::remember('countries', 500, function () {
            return Country::all();
        });

        return optional($countries->where('code', $this->to_country)->first())->sub_region;
    }

    public function getService(){
        return \ExpertShipping\Spl\Models\Service::where('code', $this->service_code)->first();
    }

    public function getTrackingLinkAttribute()
    {
        return $this->trackingLink($this->tracking_number, optional($this->carrier)->tracking_link);
    }

    public function notifications()
    {
        return $this->hasMany(ShipmentNotification::class);
    }

    public function sendNotification($data)
    {
        $type = $data['user']->is_admin ? 'admin' : 'client';
        return ShipmentNotification::create([
            'shipment_id' => $this->id,
            'notification' => $data['notification'],
            "{$type}_user_id" => $data['user']->id,
        ]);
    }
}
