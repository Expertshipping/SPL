<?php

namespace ExpertShipping\Spl\Models;

class Money
{
    private $cent;

    private function __construct($cent)
    {
        $this->cent = $cent;
    }

    public static function fromCurrencyAmount($amount)
    {
        return new static(self::normalizeAmount($amount) * 100);
    }

    public static function normalizeAmount($amount)
    {
        return floatval(str_replace(",", "", $amount));
    }
    public function inCent()
    {
        return (int) $this->cent;
    }

    public static function fromCent($cent)
    {
        return new static($cent);
    }

    public function inCurrencyAmount()
    {
        return number_format(round($this->cent / 100, 2), 2);
    }
}
