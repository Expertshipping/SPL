<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ExpertShipping\Spl\Models\LocalInvoice;

class CashRegisterSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_register_id',
        'user_id',
        'opening_amount',
        'closing_amount',
        'status',
        'closed_at',
        'notes',
        'counted_cash',
        'counted_card',
        'counted_etransfert',
        'counted_gift_card',
        'counted_anytime_mailbox',
        'manager_comment',
        'manager_validation',

        'calculated_total_cash',
        'calculated_total_card',
        'calculated_total_etransfert',
        'calculated_total_gift_card',
        'calculated_total_anytime_mailbox',
        'calculated_total_discount',
        'calculated_total_variance',
        'calculated_total_refunds',

        'manager_validation_amount',
    ];


    protected $casts = [
        'manager_validation' => 'boolean',
        'closed_at' => 'datetime',
    ];

    public function invoices()
    {
        return $this->hasMany(LocalInvoice::class);
    }

    public function invoicesWithoutDropOff()
    {
        return $this->hasMany(LocalInvoice::class)->where('total', '!=', 0);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function close($attributes)
    {
        $sumInvoices = $this->invoices->sum('total');
        $attributes['status'] = 'closed';
        $attributes['closed_at'] = now();
        $attributes['closing_amount'] = ($this->opening_amount + $sumInvoices);
        return $this->update($attributes);
    }

    public function getTotalCashAttribute()
    {
        return $this->invoicesWithoutDropOff->sum(function ($invoice) {
            if (isset($invoice->metadata['payment_details'])) {
                $cash = collect($invoice->metadata['payment_details'])
                    ->filter(function ($detail) {
                        return $detail['method'] === "Cash";
                    })->sum('amount');

                if ($cash == 0) {
                    return 0;
                }

                return $cash - $invoice->change_due;
            }
            return 0;
        });
    }

    public function getTotalCardAttribute()
    {
        return $this->invoicesWithoutDropOff->sum(function ($invoice) {
            if (isset($invoice->metadata['payment_details'])) {
                return collect($invoice->metadata['payment_details'])->filter(function ($detail) {
                    return $detail['method'] === "CreditCard" || $detail['method'] === "DebitCard" || $detail['method'] === "Card";
                })->sum('amount');
            }
            return 0;
        });
    }

    public function getTotalEtransfertAttribute()
    {
        return $this->invoicesWithoutDropOff->sum(function ($invoice) {
            if (isset($invoice->metadata['payment_details'])) {
                return collect($invoice->metadata['payment_details'])->filter(function ($detail) {
                    return $detail['method'] === "E-Transfert";
                })->sum('amount');
            }
            return 0;
        });
    }

    public function getTotalGiftCardAttribute()
    {
        return $this->invoicesWithoutDropOff->sum(function ($invoice) {
            if (isset($invoice->metadata['payment_details'])) {
                return collect($invoice->metadata['payment_details'])->filter(function ($detail) {
                    return $detail['method'] === "GiftCard";
                })->sum('amount');
            }
            return 0;
        });
    }

    public function getTotalAnytimeMailboxAttribute()
    {
        return $this->invoicesWithoutDropOff->sum(function ($invoice) {
            if (isset($invoice->metadata['payment_details'])) {
                return collect($invoice->metadata['payment_details'])->filter(function ($detail) {
                    return $detail['method'] === "MailBox";
                })->sum('amount');
            }
            return 0;
        });
    }

    public function scopeFilterByDates($query, $dates)
    {
        $query->when($dates, function ($query, $dates) {
            [$startDate, $endDate] = explode(',', $dates);
            $query->whereRaw('cast(created_at as date) between ? and ?', [$startDate, $endDate]);
        });
    }

    public function getTotalDiscountAttribute()
    {
        return $this->invoicesWithoutDropOff->sum(function ($invoice) {
            return $invoice->discount;
        });
    }

    public function getTotalVarianceAttribute()
    {
        return $this->closing_amount - ($this->counted_cash + $this->counted_card + $this->counted_etransfert + $this->counted_anytime_mailbox);
    }

    public function getDetailedTotalAttribute()
    {
        $cash = [
            'expected' => $this->calculated_total_cash,
            'declared' => $this->counted_cash - $this->opening_amount,
            'variance' => $this->calculated_total_cash - $this->counted_cash + $this->opening_amount,
        ];

        $card = [
            'expected' => $this->calculated_total_card,
            'declared' => $this->counted_card,
            'variance' => $this->calculated_total_card - $this->counted_card,
        ];

        $etransfert = [
            'expected' => $this->calculated_total_etransfert,
            'declared' => $this->counted_etransfert,
            'variance' => $this->calculated_total_etransfert - $this->counted_etransfert,
        ];

        $anytimeMailbox = [
            'expected' => $this->calculated_total_anytime_mailbox,
            'declared' => $this->counted_anytime_mailbox,
            'variance' => $this->calculated_total_anytime_mailbox - $this->counted_anytime_mailbox,
        ];

        $refunds = $this->invoicesWithoutDropOff->sum(function ($invoice) {
            return $invoice->details
                ->where('invoiceable_type', 'App\\Refund')
                ->sum(function ($detail) {
                    return $detail->total_ht_discount;
                });
        });

        $discounts = $this->total_discount;

        $totalSales = [
            'expected' => $cash['expected'] + $card['expected'] + $etransfert['expected'] + $anytimeMailbox['expected'],
            'declared' => $cash['declared'] + $card['declared'] + $etransfert['declared'] + $anytimeMailbox['declared'],
            'variance' => $cash['variance'] + $card['variance'] + $etransfert['variance'] + $anytimeMailbox['variance'],
        ];


        return [
            'cash' => $cash,
            'card' => $card,
            'etransfert' => $etransfert,
            'anytimeMailbox' => $anytimeMailbox,
            'totalSales' => $totalSales,
            'refunds' => $refunds,
            'discounts' => $discounts,
            'netSales' => $totalSales['expected'],
        ];
    }

    public function getTotalRefundsAttribute()
    {
        $refunds = $this->invoicesWithoutDropOff->sum(function ($invoice) {
            return $invoice->details
                ->where('invoiceable_type', 'App\\Refund')
                ->sum(function ($detail) {
                    return $detail->total_ht_discount;
                });
        });

        return $refunds;
    }
}
