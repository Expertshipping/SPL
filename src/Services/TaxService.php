<?php


namespace ExpertShipping\Spl\Services;


use ExpertShipping\Spl\Models\Carrier;
use ExpertShipping\Spl\Models\Money;
use ExpertShipping\Spl\Models\Shipment;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;

class TaxService
{
    public function details($amount, $state)
    {
        $taxesRates = config('statesTaxesInfo.'.$state);
        $amount = str_replace(',','',$amount);
        $preTaxPrice = round($amount / (1 + ($this->tps($state) / 100) + ($this->tvp($state) / 100)), 2);
        $tpsAmount = $this->calcTps($state, $preTaxPrice);
        $tvpAmount = $this->calcTvp($state, $preTaxPrice);
        return [
            'preTaxPrice' => $this->formatAmount(Money::fromCurrencyAmount($preTaxPrice)->inCent()),
            'tpsAmount' => Money::fromCurrencyAmount($tpsAmount)->inCent(),
            'tvpAmount' => Money::fromCurrencyAmount($tvpAmount)->inCent(),
            'taxesRatesModel' => $taxesRates,
        ];
    }

    public function getTaxes($amount, $state, $isPreTaxed=false, $toCountry='CA')
    {
        $amount = str_replace(',','',$amount);
        $preTaxPrice = 0;
        if($isPreTaxed){
            $preTaxPrice = $amount;
        }else{
            if(env('WHITE_LABEL_COUNTRY')==='CA'){
                $preTaxPrice = round($amount / (1 + ($this->tps($state) / 100) + ($this->tvp($state) / 100)), 2);
            }

            if(env('WHITE_LABEL_COUNTRY')==='MA'){
                $preTaxPrice = round($amount / (1 + (20 / 100)), 2);
            }
        }

        if($toCountry !== env('WHITE_LABEL_COUNTRY')){
            return [
                'taxes' => [],
                'preTax' => $preTaxPrice,
            ];
        }

        if(env('WHITE_LABEL_COUNTRY')==='CA'){
            $tpsAmount = $this->calcTps($state, $preTaxPrice);
            $tvpAmount = $this->calcTvp($state, $preTaxPrice);

            $taxes = [];
            if(in_array($state, ['NB', 'NL', 'NS', 'ON', 'PE'])) {
                $taxes['HST'] =  $tpsAmount + $tvpAmount;
            }else{
                $tvpName = 'PST';
                if($state === 'QC'){
                    $tvpName = 'QST';
                }
                $taxes['GST']= $tpsAmount;
                $taxes[$tvpName]= $tvpAmount;
            }
            return[
                'taxes' => $taxes,
                'preTax' => $preTaxPrice,
            ];
        }

        if(env('WHITE_LABEL_COUNTRY')==='MA'){
            $taxes = [];
            $taxes['TVA']= $preTaxPrice * 0.2;
            return[
                'taxes' => $taxes,
                'preTax' => $preTaxPrice,
            ];
        }
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
        return Cashier::formatAmount($amount, $currency ?:config('cashier.currency'));
    }

    public function rateDetails($rate, $state)
    {
        $rateDetails = collect([]);
        collect($this->details($rate, $state))
            ->each(function ($item, $key) use(&$rateDetails) {
                if(in_array($key, ['tpsAmount', 'tvpAmount'])) {
                    $rateDetails->push(['type' => $key, 'amount'=> Money::fromCent($item)->inCurrencyAmount()]);
                }
            });
    }

    public function tps($state)
    {
        $taxes = config('statesTaxesInfo.'.$state);
        if(isset($taxes['tps'])){
            return  $taxes['tps'];
        }

        return 0;

    }

    public function tvp($state)
    {
        $taxes = config('statesTaxesInfo.'.$state);

        if(isset($taxes['tvp'])){
            return $taxes['tvp'];
        }

        return 0;
    }

    public function costWithoutTax($cost, $tax, $discount)
    {
        return ($cost - $tax + $discount);
    }

    /**
     * @param $state
     * @param  float  $preTaxPrice
     *
     * @return float
     */
    public function calcTps($state, float $preTaxPrice): float
    {
        $preTaxPrice = str_replace(',','',$preTaxPrice);
        return round(($this->tps($state) * $preTaxPrice) / 100, 2);
    }

    /**
     * @param $state
     * @param  float  $preTaxPrice
     *
     * @return float
     */
    public function calcTvp($state, float $preTaxPrice): float
    {
        $preTaxPrice = str_replace(',','',$preTaxPrice);
        return round(($this->tvp($state) * $preTaxPrice) / 100, 2);
    }

    /**
     * @param $surchargeAmount
     * @param $state
     *
     * @return float
     */
    public function calcTaxedSurcharge($surchargeAmount, $state): float {
        return (float) $surchargeAmount + (
                (float) $this->calcTps($state, $surchargeAmount)
                +
                (float) $this->calcTvp($state, $surchargeAmount)
            );
    }

    public static function calculateTaxForShipment($shipment)
    {
        if($shipment->from_country === $shipment->to_country){
            $state = $shipment->to_province;
            $country = $shipment->to_country;
        }else{
            return [];
        }
        $amount = str_replace(',', '', $shipment->rate);
        $amount = str_replace(' ', '', $amount);
        $amount = doubleval($amount);

        $taxService = app(self::class);
        return $taxService->getTaxes($amount, Str::upper($state), true, $country)['taxes'];
    }

    public function getVAT($product, $country){
        // check if the product is an object
        if(!is_object($product)){
            return [];
        }
        $class = get_class($product);
        if(in_array($class, ['App\\Shipment', \ExpertShipping\Spl\Models\Shipment::class]) && $product->carrier){
            $taxes = $product->taxes ?? [];
            if(!empty($taxes)){
                if($product->carrier->slug === 'dhl' && $country === 'MA'){
                    return [
                        'TVA' => collect($taxes)->sum('amount')
                    ];
                }
                return $taxes;
            }

            $staticTaxes = Carrier::TAXES[$country][$product->carrier?->slug] ?? [];
            $variableTaxes = Carrier::VARIABLE_TAXES[$country][$product->carrier?->slug] ?? [];
            if(!empty($staticTaxes)){
                foreach ($staticTaxes as $tax => $rate){
                    $taxes[$tax] = $rate;
                }
            }

            $weight = $product->total_weight_details['billed_weight'] ?? null;
            if(!empty($variableTaxes) && $weight){
                foreach ($variableTaxes as $tax => $rates){
                    foreach ($rates as $rate){
                        if($weight >= $rate['min'] && $weight <= $rate['max']){
                            $taxes[$tax] = $rate['rate'];
                            break;
                        }
                    }
                }
            }

            return $taxes;
        }

        return [];
    }
}
