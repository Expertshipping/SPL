<?php

namespace ExpertShipping\Spl\Models\Retail;

use ExpertShipping\Spl\Models\Insurance;
use ExpertShipping\Spl\Models\InvoiceDetail;
use ExpertShipping\Spl\Models\Product;
use ExpertShipping\Spl\Models\Shipment;
use ExpertShipping\Spl\Models\User;
use ExpertShipping\Spl\Services\PayPeriodsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentCommission extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PENDING_PAYMENT = 'pending_payment';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    const DISPLAY_STATUSES = [
        self::STATUS_PENDING => 'Pending validation',
        self::STATUS_PENDING_PAYMENT => 'Pending payment',
        self::STATUS_PAID => 'Paid',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    protected $fillable = [
        'user_id',
        'company_id',
        'invoice_detail_id',
        'commission_value',
        'commission_type',
        'commission_value',
        'commission_amount',
        'commissionable_id',
        'commissionable_type',
        'status',
        'commission_id',
    ];

    public function commissionable()
    {
        return $this->morphTo();
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invoiceDetail()
    {
        return $this->belongsTo(InvoiceDetail::class);
    }

    public function getNameAttribute()
    {
        if (in_array($this->commissionable_type, [Insurance::class, 'App\Insurance'])) {
            if($this->commissionable->shipment) {
                return __("Insurance Shipment");
            }

            return __('Insurance Drop-off');
        }

        if(in_array($this->commissionable_type, [Product::class, 'App\Product'])) {
            $product = $this->commissionable()->withTrashed()->first();
            return $product->name ?? __('Product');
        }

        if(in_array($this->commissionable_type, [Shipment::class, 'App\Shipment'])) {
            return __('Shipment');
        }

        return __('Unknown');
    }

    public function getHasWarningAttribute()
    {
        $payPeriod = PayPeriodsService::getPayPeriodsByDate($this->created_at->year, $this->created_at);

        return $this->agent
                ->agentWarnings
                ->where('created_at', '>=', $payPeriod['start'])
                ->where('created_at', '<=', $payPeriod['end'])
                ->count() > 0;
    }

    public function commission(){
        return $this->belongsTo(Commissionable::class, 'commission_id');
    }

    public function getPalier1AmountAttribute()
    {
        if($this->paid_amount < 0) return $this->paid_amount;

        $commission = $this->commission;
        if($commission->commission_type == 'percentage'){
            if(!$this->invoiceDetail) return 0;
            return $this->invoiceDetail->total_ht * $commission->commission_value / 100;
        }

        return $commission->commission_value;
    }

    public function getPalier2AmountAttribute()
    {
        if($this->paid_amount < 0) return $this->paid_amount;

        $commission = $this->commission;
        if($commission->commission_type == 'percentage'){
            if(!$this->invoiceDetail) return 0;
            return $this->invoiceDetail->total_ht * $commission->commission_value_palier_2 / 100;
        }

        return $commission->commission_value_palier_2;
    }

    public function getDisplayStatusAttribute()
    {
        return __(self::DISPLAY_STATUSES[$this->status] ?? 'Unknown');
    }

    public function refund()
    {
        $this->replicate()->fill([
            'status' => self::STATUS_PENDING_PAYMENT,
            'commission_amount' => -$this->commission_amount,
            'paid_amount' => -$this->paid_amount,
        ])->save();
    }
}
