<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use ExpertShipping\Spl\Services\TaxService;
use Dompdf\Dompdf;
use Dompdf\Options;
use ExpertShipping\Spl\Models\Traits\Filterable;
use Illuminate\Support\Facades\View;
use Laravel\Cashier\Cashier;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class LocalInvoice extends Model
{
    use HasFactory;
    use SoftDeletes, Filterable;

    protected $table = 'invoices';
    protected $guarded = [];

    protected $casts = [
        'paid_at' => 'date',
        'refunded_at' => 'date',
        'canceled_at' => 'date',
        'surcharge_details' => 'array',
        'metadata' => 'array',
        'old_data' => 'boolean',
        'payment_validated' => 'boolean',
        'closed_at'   => 'date',
        'bill_to' => 'array',
    ];

    protected $appends = ['tax_rates', 'status', 'total_charged', 'taxes', 'has_bulk'];

    /**
     * Get the user that owns the invoice.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user that owns the invoice.
     */
    public function claim()
    {
        return $this->hasOne(Claim::class, 'invoice_id');
    }


    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function bulkInvoices()
    {
        return $this->hasMany(LocalInvoice::class, 'bulk_id', 'id');
    }

    public function getHasBulkAttribute()
    {
        return !is_null($this->bulk_id);
    }

    public function invoiceable()
    {
        return $this->morphTo();
    }

    public function getStatusAttribute()
    {
        if ($this->isPartiallyPaid()) {
            return 'partially paid';
        }

        if ($this->isUnderValidation()) {
            return 'under validation';
        }

        if ($this->isPaid()) {
            return 'paid';
        }

        if ($this->isCanceled()) {
            return 'canceled';
        }

        if ($this->isRefunded()) {
            return 'refunded';
        }

        if ($this->isUnPaid()) {
            return 'unpaid';
        }
    }

    private function isPaid()
    {
        return (
            !is_null($this->paid_at)
            && is_null($this->refunded_at)
            && is_null($this->canceled_at)
        ) || $this->unpaidDetails()->count() === 0;
    }

    private function unpaidDetails(){
        return $this->chargeable_details
                ->where(function($detail){
                    return !isset($detail->meta_data['payment_date']);
                });
    }

    private function isPartiallyPaid()
    {
        return $this->paid_amount && $this->paid_amount < $this->total_ht + $this->total_taxes;
    }

    private function isUnderValidation()
    {
        return !$this->isPaid() && !$this->payment_validated;
    }

    private function isUnPaid()
    {
        return is_null($this->paid_at);
    }

    private function isRefunded()
    {
        return !is_null($this->refunded_at);
    }

    private function isCanceled()
    {
        return !is_null($this->canceled_at);
    }

    public function scopeFilterBulked($query)
    {
        $query->whereNull('bulk_id');
    }

    public function getTotalChargedAttribute()
    {
        try {
            if (is_numeric($this->total)) {
                return $this->formatTotal();
            }
            return ((float) $this->total * 100);
        } catch (\Throwable $th) {
            return $this->total;
        }
    }

    public function getTaxRatesAttribute()
    {
        if ($this->shipment_id === 0) {
            return null;
        }

        $shipment = $this->shipment;
        if (!$shipment) {
            return null;
        }

        if ($shipment->to_country === 'CA' && $shipment->from_country === 'CA') {
            $taxService = resolve(TaxService::class);
            $taxDetails =  $taxService->details($this->total, $shipment->to_province);
            $taxDetails['tpsAmount'] = $taxDetails['tpsAmount'] / 100;
            $taxDetails['tvpAmount'] = $taxDetails['tvpAmount'] / 100;

            if (!$this->display_tax) {
                unset($taxDetails['tpsAmount']);
                unset($taxDetails['tvpAmount']);
            }

            return $taxDetails;
        }

        return null;
    }

    public function getTaxesAttribute()
    {
        if ($this->shipment_id === 0 || !$shipment = $this->shipment) {
            return null;
        }

        if ($shipment->to_country === 'CA' && $shipment->from_country === 'CA') {
            $taxService = resolve(TaxService::class);
            return $taxService->getTaxes($this->total, $shipment->to_province, false);
        }

        return [
            'taxes' => null,
            'preTax' => $this->total,
        ];
    }

    /**
     * Format the given amount into a displayable currency.
     *
     * @param  int  $amount
     * @param $currency
     *
     * @return string
     */
    public function formatAmount($amount, $currency = null)
    {
        return Cashier::formatAmount($amount, $currency ?: config('cashier.currency'));
    }

    public function isCancelable()
    {
        return !!$this->paid_at;
    }

    public function scopeFilterByTracking($query, $term)
    {
        $query->when($term, function ($query, $term) {
            $query->whereHas('shipment', function ($query) use ($term) {
                $query->where('tracking_number', $term);
            })
                ->orWhereHas('bulkInvoices', function ($query) use ($term) {
                    $query->whereHas('shipment', function ($query) use ($term) {
                        $query->where('tracking_number', $term);
                    });
                });
        });
    }
    public function scopeFilterByRef($query, $term)
    {
        $query->when($term, function ($query, $term) {
            collect(str_getcsv($term, ' ', '"'))->filter()->each(function ($term) use ($query) {
                $term = $term . '%';
                $query->whereHas('shipment', function ($query) use ($term) {
                    $query->where('reference_value', 'like', $term);
                });
            });
        });
    }

    public function scopeFilterById($query, $invoiceNumber)
    {
        $query->when($invoiceNumber, function ($query, $invoiceNumber) {
            $query->whereId($invoiceNumber);
        });
    }

    public function scopeFilterByStatus($query, $status)
    {
        $query->when($status, function ($query, $status) {
            $query->when($status === 'paid', function ($query) {
                $query->whereNotNull('paid_at')
                    ->whereNull('refunded_at');
            });

            $query->when($status === 'unpaid', function ($query) {
                $query->whereNull('paid_at')
                    ->whereNull('refunded_at');
            });

            $query->when($status === 'refunded', function ($query) {
                $query->whereNotNull('refunded_at');
            });
        });
    }

    public function scopeFilterByDates($query, $dates)
    {
        $query->when($dates, function ($query, $dates) {
            [$startDate, $endDate] = explode(',', $dates);
            $query->whereRaw('cast(created_at as date) between ? and ?', [$startDate, $endDate]);
        });
    }

    public function scopeFilterByCompany($query, $company)
    {
        $query->when($company, function ($query, $company) {
            $query->where('company_id', $company);
        });
    }

    /**
     * Create an invoice download response.
     *
     * @param  array  $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(array $data)
    {
        // $filename = $data['product'].'_'.$this->date()->month.'_'.$this->date()->year;
        $filename = $this->id;

        return $this->downloadAs($filename, $data);
    }

    /**
     * Create an invoice download response with a specific filename.
     *
     * @param  string  $filename
     * @param  array  $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadAs($filename, array $data)
    {
        return $this->view($data);
        return new Response($this->pdf($data), 200, [
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"',
            'Content-Transfer-Encoding' => 'binary',
            'Content-Type' => 'application/pdf',
            'X-Vapor-Base64-Encode' => 'True',
        ]);
    }

    /**
     * Capture the invoice as a PDF and return the raw bytes.
     *
     * @param  array  $data
     * @return string
     */
    public function pdf(array $data)
    {
        if (!defined('DOMPDF_ENABLE_AUTOLOAD')) {
            define('DOMPDF_ENABLE_AUTOLOAD', false);
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $dompdf->setHttpContext($context);

        $dompdf->setPaper('a4');
        $dompdf->loadHtml($this->view($data)->render());
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Get the View instance for the invoice.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\View\View
     */
    public function view(array $data)
    {
        return View::make('spl::invoices.local-invoice', array_merge($data, [
            'invoice' => $this,
            'invoiceable' => $this->invoiceable_type === null ? null : $this->invoiceable,
            'owner' => $this->user,
            'user' => $this->user,
        ]));
    }

    /**
     * Get a Carbon date for the invoice.
     *
     * @param  \DateTimeZone|string  $timezone
     * @return \Carbon\Carbon
     */
    public function date($timezone = null)
    {
        $carbon = $this->paid_at ?? $this->created_at;

        return $timezone ? $carbon->setTimezone($timezone) : $carbon;
    }

    /**
     * Get the total amount that was paid (or will be paid).
     *
     * @return string
     */
    public function formatTotal()
    {
        return $this->formatAmount((float) $this->total * 100);
    }

    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_id');
    }

    public function posDetails()
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_id')->where('pos', 1);
    }

    public function saleDetails()
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_id')->where('pos', 0);
    }

    public function getChangeDueAttribute()
    {
        $details = collect($this->metadata['payment_details']);
        return $details->sum('amount') - $this->total;
    }

    public function cashRegisterSession()
    {
        return $this->belongsTo(CashRegisterSession::class);
    }

    public function getSumTaxAttribute()
    {
        return $this->details->sum(function ($detail) {
            if ($detail->taxes) {
                return array_sum($detail->taxes);
            } else {
                return 0;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function store()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function getTaxesWithoutShipmentAttribute()
    {
        // $taxService = resolve(TaxService::class);
        // return $taxService->getTaxes($this->total, $this->company->state, true);
        $taxes = [];
        $sumTaxes = 0;
        $this->details->each(function ($detail) use (&$taxes, &$sumTaxes) {
            if (is_array($detail->taxes)) {
                foreach ($detail->taxes as $key => $value) {
                    if (isset($taxes[$key])) {
                        $taxes[$key] += $value;
                    } else {
                        $taxes[$key] = $value;
                    }
                    $sumTaxes += $value;
                }
            }
        });

        return [
            'taxes' => $taxes,
            'preTax' => $this->total - $sumTaxes
        ];
    }

    // public function getTotalTaxesAttribute(){
    //     return $this->details
    //     ->sum(function($detail){
    //         return $detail->total_taxes;
    //     });
    // }

    public function getDiscountAttribute()
    {
        return $this->details
            ->sum(function ($detail) {
                return $detail->discount_value;
            });
    }

    public function getTotalHtDiscountAttribute()
    {
        return $this->details
            ->sum(function ($detail) {
                return $detail->total_ht_discount;
            });
    }

    public function getDiscountReasonAttribute()
    {
        return $this->details
            ->whereNotNull('discount_id')
            ->map(function ($detail) {
                return [
                    'name' => $detail->discount->name,
                ];
            })
            ->unique()
            ->toArray();
    }

    public function generateToken()
    {
        if (!$this->token) {
            $this->token = Str::random(15);
            $this->save();
        }
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'invoice_id');
    }


    public function getPhoneAndEmailFromDetails()
    {
        $email = null;
        $phone = null;
        $carrierId = null;

        $this->details->each(function ($detail) use (&$email, &$phone, &$carrierId) {
            if ($detail->invoiceable_type === Shipment::class) {
                if ($shipment = $detail->invoiceable) {
                    $email = $shipment->from_email;
                    $phone = $shipment->from_phone;
                    $carrierId = $shipment->carrier_id;
                    return false;
                }
            }

            if ($detail->invoiceable_type === Insurance::class) {
                if ($insurance = $detail->invoiceable) {
                    $email = $insurance->email;
                    $phone = $insurance->phone;
                    $carrierId = $insurance->carrier_id;
                    return false;
                }
            }
        });

        if (!$email && $leadEmail = $this->leads()->where('type', 'email')->first()) {
            $email = $leadEmail->value;
        }

        if (!$phone && $leadPhone = $this->leads()->where('type', 'phone')->first()) {
            $phone = $leadPhone->value;
        }

        return [
            'email' => $email,
            'phone' => $phone,
            'carrierId' => $carrierId,
        ];
    }

    public function clientExperienceDetail()
    {
        return $this->morphOne(ClientExperienceDetail::class, 'reviewable');
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function agentTip()
    {
        return $this->hasOne(AgentTip::class, 'invoice_id');
    }

    public static function boot()
    {
        parent::boot();

        // Auto add invoiceNumber when creating a new invoice (order of the invoice in current year)
        static::creating(function ($invoice) {
            $invoice->invoice_number = $invoice->generateInvoiceNumber();
        });

        static::deleting(function ($user) {
            $user->details()->delete();
            $user->leads()->delete();
            $user->clientExperienceDetail()->delete();
            $user->agentTip()->delete();
        });
    }

    public function consumer()
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    public function totalUnpaid()
    {
        return $this->total - $this->details()->whereNotNull('meta_data->payment_intent_id')->sum('price');
    }

    public function updateTotal($pos)
    {
        $details = $this->details()
            ->where('pos', $pos)
            ->whereNull('canceled_at')
            ->get();

        $totalPaid = 0;

        $details->each(function ($detail) use (&$totalPaid) {
            if(isset($detail->meta_data['payment_date'])){
                $totalPaid += $detail->price;
            }
            $detail->update([
                'total_ht' => $detail->ht_discount,
                'total_taxes' => $detail->sum_taxes,
                'total_discount' => $detail->discount_value,
            ]);
        });

        $total = $details->sum('total_ht') + $details->sum('total_taxes');

        $this->update([
            'total' => $total,
            'total_ht' => $details->sum('total_ht'),
            'total_taxes' => $details->sum('total_taxes'),
            'total_discount' => $details->sum('total_discount'),
            'paid_at' => $totalPaid === $total ? now() : null,
        ]);
    }

    public function getProductAttribute()
    {
        if ($this->invoiceable_type === Shipment::class) {
            return __("Shipment") . " " . $this->invoiceable->tracking_number;
        }

        if ($this->invoiceable_type === Insurance::class) {
            return __("Insurance") . " " . $this->invoiceable->tracking_number;
        }

        if ($this->surcharge_details->count() > 0) {
            if ($this->shipment) {
                return __("Surcharge Shipment") . " " . $this->shipment->tracking_number;
            }

            return __("Surcharge");
        }

        return "Other Products";
    }

    public function getCategoryAttribute()
    {
        if ($this->invoiceable_type === Shipment::class) {
            return __("Shipment") . " " . ($this->invoiceable->carrier->name ?? '');
        }

        if ($this->invoiceable_type === Insurance::class) {
            return __("Insurance");
        }

        if ($this->surcharge_details->count() > 0) {
            if ($this->shipment) {
                return __("Shipment") . " " . ($this->invoiceable->carrier->name ?? '');
            }

            return __("-");
        }

        return "Other Products";
    }

    public function getParentCategoryAttribute()
    {
        if ($this->invoiceable_type === Shipment::class) {
            return __("Shipment");
        }

        if ($this->invoiceable_type === Insurance::class) {
            return __("Insurance");
        }

        if ($this->surcharge_details->count() > 0) {
            if ($this->shipment) {
                return __("Shipment");
            }
            return __("-");
        }

        return "Other Products";
    }

    public function getTypeAttribute()
    {
        if ($this->surcharge_details->count() > 0) {
            return __("Surcharge");
        }

        return "Sale";
    }

    public function getTotalPaidAmountAttribute()
    {
        $total = $this->details
            ->where('pos', 0)
            ->whereNull('canceled_at')
            ->whereNotNull('meta_data.payment_date')
            ->sum('price');

        if($total === 0 && $this->paid_at){
            $total = $this->total;
        }

        return $total;
    }

    public function getTotalDueAmountAttribute()
    {
        return $this->details
            ->where('pos', 0)
            ->whereNull('canceled_at')
            ->whereNull('meta_data.payment_date')
            ->sum('price');
    }

    public function getTotalFreightChargesAttribute(){
        $attribute = $this->company->is_retail_reseller ? 'retail_reseller_rate_details' : 'rate_details';
        return InvoiceDetail::query()
            ->where('invoice_id', $this->id)
            ->where('pos', 0)
            ->whereNull('canceled_at')
            ->where('invoiceable_type', 'App\\Shipment')
            ->get()
            ->sum(function($detail) use ($attribute){
                return collect($detail->invoiceable->{$attribute})
                    ->whereIn('type', Shipment::FREIGHT_CHARGES)
                    ->sum('amount');
            });
    }

    public function getTotalFuelChargesAttribute(){
        $attribute = $this->company->is_retail_reseller ? 'retail_reseller_rate_details' : 'rate_details';
        return InvoiceDetail::query()
            ->where('invoice_id', $this->id)
            ->where('pos', 0)
            ->whereNull('canceled_at')
            ->where('invoiceable_type', 'App\\Shipment')
            ->get()
            ->sum(function($detail) use ($attribute){
                return collect($detail->invoiceable->{$attribute})
                    ->whereIn('type', Shipment::FUEL_CHARGES)
                    ->sum('amount');
            });
    }


    public function getTotalTaxesChargesAttribute(){
        $attribute = $this->company->is_retail_reseller ? 'retail_reseller_rate_details' : 'rate_details';
        return InvoiceDetail::query()
            ->where('invoice_id', $this->id)
            ->where('pos', 0)
            ->whereNull('canceled_at')
            ->where('invoiceable_type', 'App\\Shipment')
            ->get()
            ->sum(function($detail) use ($attribute){
                return collect($detail->invoiceable->{$attribute})
                    ->whereIn('type', Shipment::TAXES_CHARGES)
                    ->sum('amount');
            });
    }


    public function getTotalOtherChargesAttribute(){
        $attribute = $this->company->is_retail_reseller ? 'retail_reseller_rate_details' : 'rate_details';
        $allCharges = InvoiceDetail::query()
            ->where('invoice_id', $this->id)
            ->where('pos', 0)
            ->whereNull('canceled_at')
            ->where('invoiceable_type', 'App\\Shipment')
            ->get()
            ->sum(function($detail) use ($attribute){
                return collect($detail->invoiceable->{$attribute})->sum('amount');
            });

        return $allCharges - $this->total_freight_charges - $this->total_fuel_charges - $this->total_taxes_charges;
    }

    public function getTotalSurchargesAttribute(){
        return InvoiceDetail::query()
            ->where('invoice_id', $this->id)
            ->where('pos', 0)
            ->whereNull('canceled_at')
            ->where('invoiceable_type', 'App\\ShipmentSurcharge')
            ->sum('price');
    }

    public function getDetailsTaxesAttribute(){
        $taxes = [
            'HST' => 0,
            'PST' => 0,
            'QST' => 0,
            'GST' => 0,
        ];

        $preTax = 0;

        InvoiceDetail::query()
            ->where('invoice_id', $this->id)
            ->where('pos', 0)
            ->whereNull('canceled_at')
            ->each(function($detail) use (&$preTax, &$taxes){
                $shipment = $detail->invoiceable;

                if ($shipment->to_country === 'CA' && $shipment->from_country === 'CA') {
                    $taxService = resolve(TaxService::class);
                    $t = $taxService->getTaxes($detail->price, $shipment->to_province, false);

                    $taxes['HST'] += $t['taxes']['HST'] ?? 0;
                    $taxes['PST'] += $t['taxes']['PST'] ?? 0;
                    $taxes['QST'] += $t['taxes']['QST'] ?? 0;
                    $taxes['GST'] += $t['taxes']['GST'] ?? 0;
                    $preTax += $t['preTax'];
                }else{
                    $preTax += $detail->price;
                }
            });

        $tx = new \stdClass();
        $tx->taxes = $taxes;
        $tx->preTax = $preTax;

        return $tx;
    }

    public function getChargeableDetailsAttribute(){
        return InvoiceDetail::query()
            ->where('invoice_id', $this->id)
            ->where('pos', 0)
            ->whereNull('canceled_at')
            ->get();
    }

    public function getTokenIdAttribute()
    {
        return encrypt($this->id);
    }


    // Invoice number getter if the field invoice_number is null then return the id
    public function getInvoiceNumberAttribute($value)
    {
        return $value ?? $this->id;
    }

    public function generateInvoiceNumber()
    {
        $year = now()->year;
        $number = self::query()
            ->withTrashed()
            ->whereYear('created_at', now()->year)
            ->count();
        return $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
