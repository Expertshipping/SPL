<?php
namespace ExpertShipping\Spl\Helpers;

class Money
{
    const MONEYSYMBOLS = [
        'CAD' => '$', // Canadian Dollar
        'MAD' => 'د.م.', // Moroccan Dirham
        'USD' => '$', // US Dollar
        'EUR' => '€', // Euro
        'GBP' => '£', // British Pound
        'AUD' => '$', // Australian Dollar
        'NZD' => '$', // New Zealand Dollar
        'JPY' => '¥', // Japanese Yen
        'CNY' => '¥', // Chinese Yuan
        'INR' => '₹', // Indian Rupee
        'RUB' => '₽', // Russian Ruble
        'ZAR' => 'R', // South African Rand
        'BRL' => 'R$', // Brazilian Real,
    ];
    public static function format($amount, $currency="CAD")
    {
        $amount = number_format(round($amount, 2), 2);
        $amount = str_replace(',','',$amount);

        try {
            $currencySymbol = self::MONEYSYMBOLS[$currency];
            $money = "$currencySymbol$amount";
            if($amount<0){
                $money = "- $currencySymbol".abs($amount);
            }

            return "$money $currency";
        } catch (\Throwable $th) {
            return "$currency$amount";
        }
    }
}
