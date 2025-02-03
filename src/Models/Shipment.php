<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Helpers\Helper;
use ExpertShipping\Spl\Jobs\CalculateDistanceBetweenStoreAndClientForRetailShipments;
use ExpertShipping\Spl\Models\Models\ReferralPayout;
use ExpertShipping\Spl\Models\Retail\InsuranceSuggestion;
use ExpertShipping\Spl\Models\Traits\HasComputedData;
use Illuminate\Support\Facades\Cache;
use ExpertShipping\Spl\Models\Traits\HasTrackingLink;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Shipment extends ComputedModel
{
    use HasTrackingLink;
    use HasComputedData;

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

    protected $guarded = [];

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

        'failed_aramex_hub_label' => 'boolean',
        'failed_pickup' => 'boolean',
        'retail_reseller_rate_details' => 'array',
        'tracking_numbers' => 'array',
        'is_manual_shipment' => 'boolean',
        'taxes' => 'array',
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
                        ->orWhere('tracking_numbers', 'like', "%$term%")
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

    public function getTotalWeightDetailsAttribute()
    {
        $packageTotalWeight = collect($this->package->meta_data)->sum('weight');
        $service = $this->getService();

        $totalVolumetricWeight = 0;
        foreach ($this->package->meta_data as $package) {
            if(isset($package->weight) && isset($package->length) && isset($package->width) && isset($package->height)){
                $totalVolumetricWeight += Helper::calculateVolumetricWeightByUnit(
                    $package->length,
                    $package->width,
                    $package->height,
                    $this->carrier->slug,
                    $this->package->weight_unit,
                    $service->transport_type,
                );
            }
        }

        return [
            'weight' => $packageTotalWeight,
            'volumetric_weight' => $totalVolumetricWeight,
            'billed_weight' => max($packageTotalWeight, $totalVolumetricWeight),
            'weight_unit' => $this->package->weight_unit,
            'billed_method' => $packageTotalWeight < $totalVolumetricWeight ? 'Volumetric' : 'Actual Weight',
        ];
    }

    public function generatePickupPayload($request){
        return [
            'pickup_date' => $request['pickup_date'] ?? now()->format('Y-m-d'),
            'pickup_working_hours' => $request['pickup_working_hours'] ?? '09:00',
            'pickup_closing_hours' => $request['pickup_closing_hours'] ?? '17:00',
            'pickup_company' => $this->from_company,
            'pickup_full_name' => $this->from_name,
            'pickup_address' => $this->from_address_1,
            'pickup_addr2' => $this->from_address_2,
            'pickup_city' => $this->from_city,
            'pickup_province' => $this->from_province,
            'pickup_zipcode' => $this->from_zip_code,
            'pickup_country' => $this->from_country,
            'pickup_phone_number' => $request['pickup_phone'] ?? $this->from_phone,
            'pickup_email' => $this->from_email,
            'carrierAccounts' => $this->user->carrierAccounts(),
            'total_pieces' => count($this->package->meta_data) ?? 1,
        ];
    }

    public function scopeExportShipments($query)
    {
        return $query->whereHas('company', function ($query) {
            $query->whereColumn('shipments.from_country', 'companies.country')
                ->whereColumn('shipments.to_country', '!=', 'companies.country');
        });
    }

    public function scopeImportShipments($query)
    {
        return $query->whereHas('company', function ($query) {
            $query->whereColumn('shipments.to_country', 'companies.country')
                ->whereColumn('shipments.from_country', '!=', 'companies.country');
        });
    }

    public function insuranceSuggestion()
    {
        return $this->hasOne(InsuranceSuggestion::class);
    }

    public function quote() {
        return $this->hasOne(Quote::class);
    }

    public function sentCoupon() {
        return $this->belongsTo(SentCoupon::class, 'coupon_id');
    }

    public function getSalePriceAttribute()
    {
        if(
            $this->company &&
            ($this->company->account_type === 'business' || $this->company->is_retail_reseller)
        ){
            $relation = 'saleDetail';
        }else{
            $relation = 'receiptDetail';
        }

        if($this->{$relation} && $this->editedShipment){
            return $this->{$relation}->total_ht + ($this->editedShipment->{$relation}->total_ht ?? 0);
        }

        if($this->{$relation}){
            return $this->{$relation}->total_ht;
        }

        return null;
    }

    public function getRefundPriceAttribute()
    {
        if (isset($this->computed_data['refund_price'])) {
            return $this->computed_data['refund_price'];
        }
        return $this->setRefundPrice();
    }

    public function setRefundPrice()
    {
        if($this->company?->account_type === 'business')
        {
            $this->setComputedData('refund_price', 0);
            return 0;
        }
        $cacheKey = "refund_prices";
        $refundCache = Cache::remember($cacheKey, now()->addMinutes(30), function () {
            return Refund::query()
                ->select('details')
                ->whereRaw('JSON_CONTAINS(details, ?)', ['{"invoiceable_type": "App\\\\Shipment"}'])
                ->get();
        });
        $refundValue = $refundCache
            ->sum(function ($refund) {
                if(isset($refund->details)){
                    $price = 0;
                    foreach ($refund->details as $refundDetail) {
                        if($refundDetail['invoiceable_id'] === $this->id){
                            $price += $refundDetail['price'];
                        }
                    }
                    return $price;
                }
            });

        $this->setComputedData('refund_price', $refundValue);
        return $refundValue;
    }

    public function getSalePriceWithTaxesAttribute()
    {
        if (isset($this->computed_data['sale_price_with_taxes'])) {
            return $this->computed_data['sale_price_with_taxes'];
        }
        return $this->setSalePriceWithTaxes();
    }

    public function setSalePriceWithTaxes()
    {
        $total = 0;
        if($this->invoice){
            $total = $this->invoice->total;
        } else {
            if(
                $this->company &&
                ($this->company->account_type === 'business' || $this->company->is_retail_reseller)
            ){
                $total = DB::table('invoice_details')
                    ->where('pos', false)
                    ->where('invoiceable_id', $this->id)
                    ->where('invoiceable_type', 'App\\Shipment')
                    ->selectRaw('SUM(total_ht + total_taxes) as total')
                    ->value('total');
            } else {
                $total = DB::table('invoice_details')
                ->where('pos', true)
                ->where('invoiceable_id', $this->id)
                ->where('invoiceable_type', 'App\\Shipment')
                ->selectRaw('SUM(total_ht + total_taxes) as total')
                ->value('total');            }
        }
        $this->setComputedData('sale_price_with_taxes', $total);
        return $total;
    }


    public function getMargeAttribute()
    {
        if ($this->getComputedData('marge') !== null) {
            return $this->getComputedData('marge');
        }
        return $this->setMarge();
    }

    public function setMarge()
    {
        $price = 0;

        if($this->sale_price != null){
            $price = $this->sale_price;
        } else {
            if($this->invoice){
                $price = $this->invoice->total;
            }
        }
        $marge = round(($price ?? 0) - ($this->carrier_charged_price + ($this->refund_price ?? 0)), 2);

        $this->setComputedData('marge', $marge);

        return $marge;
    }



    public function getCarrierChargedPriceAttribute(){
        if ($this->hasComputedData('carrier_charged_price')) {
            return $this->getComputedData('carrier_charged_price');
        }
        return $this->setCarrierChargedPrice();
    }

    public function setCarrierChargedPrice(){
        $total = DB::table('carrier_invoice_shipments')
        ->where('shipment_id', $this->id)
        ->selectRaw("
        SUM(
            JSON_UNQUOTE(JSON_EXTRACT(taxes, '$.gst')) +
            JSON_UNQUOTE(JSON_EXTRACT(taxes, '$.hst')) +
            JSON_UNQUOTE(JSON_EXTRACT(taxes, '$.pst')) +
            JSON_UNQUOTE(JSON_EXTRACT(taxes, '$.qst')) +
            net_charge +
            net_surcharge
        ) AS total
        ")
        ->value('total');

        $this->setComputedData('carrier_charged_price', $total ?? 0);

        return $total ?? 0;
    }

    public static function afterModelSaved($model) {
        Log::info('Shipment saved: ' . $model->id);
    }
    public static function afterModelDeleted($model) {
        Log::info('Shipment deleted: ' . $model->id);
    }

    protected static function getPivotTrackedRelations(): array
    {
        return [ 'carrierInvoices' ];
    }

    protected static function getRelationTrackedRelations(): array
    {
        return [ 'invoiceDetail', 'invoice' ];
    }

    public function onPivotChanged(string $relation, string $event)
    {
        if ($relation === 'carrierInvoices') {
            $this->setCarrierChargedPrice();
        }

        if(in_array($relation, ['invoiceDetail', 'invoice'])){
            $this->setSalePriceWithTaxes();
            $this->setMarge();
        }

    }
}
