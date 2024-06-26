<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Models\Retail\AgentCommission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'invoiceable_type',
        'invoiceable_id',
        'price',
        'quantity',
        'discount_id',
        'taxes',
        'meta_data',
        'refund_id',
        'correction_refund_id',
        'total_ht',
        'total_taxes',
        'total_discount',
        'moved',
        'canceled_at',
        'pos',
    ];



    protected $casts = [
        'taxes' => 'array',
        'meta_data' => 'array',
        'moved' => 'boolean',
        'canceled_at' => 'datetime',
        'pos' => 'boolean',
    ];

    protected $with = ['discount'];

    public function invoiceable()
    {
        return $this->morphTo();
    }

    public function invoice()
    {
        return $this->belongsTo(LocalInvoice::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function getProductNameAttribute()
    {
        if ($this->invoiceable_type === Shipment::class) {
            if (!$this->invoiceable) {
                return "Shipment " . $this->meta_data['carrier'];
            }
            return "Tracking " . ($this->invoiceable->carrier->name ?? '-') . " : " . ($this->invoiceable->tracking_number ?? '-');
        }

        if ($this->invoiceable_type === Insurance::class) {
            return "Insurance : " . $this->invoiceable->transaction_number;
        }

        if ($this->invoiceable_type === "App\\Product") {
            if (!$this->invoiceable) {
                return "Other services";
            }
            return $this->invoiceable->name ?? '-';
        }

        if ($this->invoiceable_type === Refund::class) {
            if (!$this->invoiceable) {
                return "Refund";
            }
            $refundedProducts = collect();
            foreach ($this->invoiceable->details as $detail) {
                if ($refundedProduct = $detail['invoiceable_type']::find($detail['invoiceable_id'])) {
                    if ($detail['invoiceable_type'] === Shipment::class) {
                        $refundedProducts->push("Refund Tracking : {$refundedProduct->tracking_number}");
                    }

                    if ($detail['invoiceable_type'] === Insurance::class) {
                        $refundedProducts->push("Refund Insurance : {$refundedProduct->transaction_number}");
                    }

                    if ($detail['invoiceable_type'] === Product::class) {
                        $refundedProducts->push("Refund " . ($refundedProduct->name ?? '-'));
                    }
                }
            }

            return $refundedProducts->toArray();
        }


        if ($this->invoiceable_type === CorrectionRefund::class) {
            if (!$this->invoiceable) {
                return "Correction Refund";
            }

            return "Correction Refund " . optional(optional($this->invoiceable->refund)->invoiceDetail)->invoice_id;
        }

        return "<>";
    }

    public function getCategoryNameAttribute()
    {
        if ($this->invoiceable_type === Shipment::class) {
            return "Shipment " . ($this->invoiceable->carrier->name ?? '-');
        }

        if ($this->invoiceable_type === Insurance::class) {
            return "Insurance";
        }

        if ($this->invoiceable_type === Product::class) {
            if (!$this->invoiceable) {
                return "Other services";
            }
            if ($this->invoiceable->category)
                return $this->invoiceable->category->name ?? '-';
        }

        if ($this->invoiceable_type === Refund::class) {
            if (!$this->invoiceable) {
                return "Refund";
            }
            $refundedProducts = collect();
            foreach ($this->invoiceable->details as $detail) {
                if ($refundedProduct = $detail['invoiceable_type']::find($detail['invoiceable_id'])) {
                    if ($detail['invoiceable_type'] === Shipment::class) {
                        $refundedProducts->push("Shipment " . ($refundedProduct->carrier->name ?? '-'));
                    }

                    if ($detail['invoiceable_type'] === Insurance::class) {
                        $refundedProducts->push("Insurance");
                    }

                    if ($detail['invoiceable_type'] === Product::class) {
                        $refundedProducts->push("Other services " . ($refundedProduct->category->name ?? '-'));
                    }
                }
            }

            return $refundedProducts->first();
        }

        if ($this->invoiceable_type === CorrectionRefund::class) {
            return "Correction Refund";
        }

        return false;
    }

    public function getParentCategoryNameAttribute()
    {
        if ($this->invoiceable_type === Shipment::class) {
            return "Shipment";
        }

        if ($this->invoiceable_type === Insurance::class) {
            return "Insurance";
        }

        if ($this->invoiceable_type === Product::class) {
            if (!$this->invoiceable) {
                return "Other services";
            }

            return $this->invoiceable->category->parent->name ?? $this->invoiceable->category->name ?? "Other services";
        }

        if ($this->invoiceable_type === Refund::class) {
            if (!$this->invoiceable) {
                return "Refund";
            }
            $refundedProducts = collect();
            foreach ($this->invoiceable->details as $detail) {
                if ($refundedProduct = $detail['invoiceable_type']::find($detail['invoiceable_id'])) {
                    if ($detail['invoiceable_type'] === Shipment::class) {
                        $refundedProducts->push("Shipment");
                    }

                    if ($detail['invoiceable_type'] === Insurance::class) {
                        $refundedProducts->push("Insurance");
                    }

                    if ($detail['invoiceable_type'] === Product::class) {
                        $refundedProducts->push("Other services");
                    }
                }
            }

            return $refundedProducts->first();
        }

        if ($this->invoiceable_type === CorrectionRefund::class) {
            return "Correction Refund";
        }

        return false;
    }

    public function getProductNameWithCategoryAttribute()
    {
        if ($this->invoiceable_type === Shipment::class) {
            if (!$this->invoiceable) {
                return "Shipment " . $this->meta_data['carrier'];
            }
            return "Tracking " . ($this->invoiceable->carrier->name ?? '-') . " : " . ($this->invoiceable->tracking_number ?? '-');
        }

        if ($this->invoiceable_type === Insurance::class) {
            return "Insurance : " . $this->invoiceable->transaction_number;
        }

        if ($this->invoiceable_type === Product::class) {
            if (!$this->invoiceable) {
                return "Other services";
            }
            return ($this->invoiceable->category->name ?? '-') . " " . ($this->invoiceable->name ?? '-');
        }

        if ($this->invoiceable_type === Refund::class) {
            if (!$this->invoiceable) {
                return "Refund";
            }
            $refundedProducts = collect();
            foreach ($this->invoiceable->details as $detail) {
                if ($refundedProduct = $detail['invoiceable_type']::find($detail['invoiceable_id'])) {
                    if ($detail['invoiceable_type'] === Shipment::class) {
                        $refundedProducts->push("Refund Tracking : {$refundedProduct->tracking_number}");
                    }

                    if ($detail['invoiceable_type'] === Insurance::class) {
                        $refundedProducts->push("Refund Insurance : {$refundedProduct->transaction_number}");
                    }

                    if ($detail['invoiceable_type'] === Product::class) {
                        $refundedProducts->push("Refund " . ($refundedProduct->name ?? '-'));
                    }
                }
            }

            return $refundedProducts->toArray();
        }

        return "<>";
    }

    public function getDiscountNameAttribute()
    {
        if ($this->discount) {
            return $this->discount->name ?? '-';
        }

        return "";
    }

    public function getSumTaxesAttribute()
    {
        if (is_array($this->taxes)) {
            return array_sum($this->taxes);
        }
        return $this->taxes;
    }

    public function getTotalAttribute()
    {
        return $this->total_ht + $this->total_taxes;
    }

    public function getHtAttribute()
    {
        return $this->price * $this->quantity;
    }

    public function getTotalDiscountFieldAttribute()
    {
        return $this->total - $this->discount_value;
    }

    public function getHtDiscountAttribute()
    {
        return $this->ht - $this->discount_value;
    }

    public function getDiscountValueAttribute()
    {
        if ($this->discount && isset($this->meta_data['discount_variable']) && isset($this->meta_data['discount_type'])) {
            if ($this->meta_data['discount_type'] === "dollar") {
                return $this->meta_data['discount_variable'];
            } else {
                return ((float) $this->meta_data['discount_variable'] / 100) * $this->ht;
            }
        } else if ($discount = $this->discount) {
            if ($discount->type === "dollar") {
                $discountValue = $discount->value;
            } else {
                $discountValue = ($discount->value / 100) * $this->ht;
            }
            return $discountValue;
        }

        return 0;
    }

    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }

    public function agentCommissions()
    {
        return $this->hasMany(AgentCommission::class);
    }
}
