<?php
namespace ExpertShipping\Spl\Services;

use ExpertShipping\Spl\Models\Money;
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
        $amount = str_replace(',','', $amount);
        $preTaxPrice = 0;
        if($isPreTaxed){
            $preTaxPrice = $amount;
        }else{
            if((auth()?->user()?->company?->platformCountry?->code ?? config('app.white_label.country')) === 'CA'){
                $preTaxPrice = round($amount / (1 + ($this->tps($state) / 100) + ($this->tvp($state) / 100)), 2);
            }
            if((auth()?->user()?->company?->platformCountry?->code ?? config('app.white_label.country')) === 'MA'){
                $preTaxPrice = round($amount / (1 + (20 / 100)), 2);
            }

            if(env('WHITE_LABEL_COUNTRY') === 'US'){
                $salesTaxRate = $this->getUsSalesTaxRate($state);
                $preTaxPrice = round($amount / (1 + ($salesTaxRate / 100)), 2);
            }
        }

        if($toCountry !== (auth()?->user()?->company?->platformCountry?->code ?? config('app.white_label.country'))){
            return [
                'taxes' => [],
                'preTax' => $preTaxPrice,
            ];
        }

        if((auth()?->user()?->company?->platformCountry?->code  ?? config('app.white_label.country'))==='CA'){
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

        if((auth()?->user()?->company?->platformCountry?->code  ?? config('app.white_label.country'))==='MA'){
            $taxes = [];
            $taxes['TVA']= $preTaxPrice * 0.2;
            return[
                'taxes' => $taxes,
                'preTax' => $preTaxPrice,
            ];
        }

        if(config('app.white_label.country') === 'US'){
            $taxes = [];
            $salesTaxRate = $this->getUsSalesTaxRate($state);
            if($salesTaxRate > 0){
                $taxes['Sales Tax']= round($preTaxPrice * ($salesTaxRate / 100), 2);
            }
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
        if(env('WHITE_LABEL_COUNTRY')==='CA'){
            return (float) $surchargeAmount + (
                    (float) $this->calcTps($state, $surchargeAmount)
                    +
                    (float) $this->calcTvp($state, $surchargeAmount)
                );
        }

        if(env('WHITE_LABEL_COUNTRY')==='US'){
            return (float) $surchargeAmount + (float) $this->calcUsSalesTax($state, $surchargeAmount);
        }

        // Pour les autres pays ou si aucun pays n'est configuré
        return (float) $surchargeAmount;
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

    /**
     * Get US sales tax rate for a given state
     *
     * @param string $state
     * @return float
     */
    public function getUsSalesTaxRate($state): float
    {
        // Taux de taxe de vente par état américain (taux de base de l'état)
        // Ces taux peuvent varier selon les municipalités locales
        $usTaxRates = [
            'AL' => 4.0,    // Alabama
            'AK' => 0.0,    // Alaska (pas de taxe d'état)
            'AZ' => 5.6,    // Arizona
            'AR' => 6.5,    // Arkansas
            'CA' => 7.25,   // Californie
            'CO' => 2.9,    // Colorado
            'CT' => 6.35,   // Connecticut
            'DE' => 0.0,    // Delaware (pas de taxe de vente)
            'FL' => 6.0,    // Floride
            'GA' => 4.0,    // Géorgie
            'HI' => 4.0,    // Hawaï
            'ID' => 6.0,    // Idaho
            'IL' => 6.25,   // Illinois
            'IN' => 7.0,    // Indiana
            'IA' => 6.0,    // Iowa
            'KS' => 6.5,    // Kansas
            'KY' => 6.0,    // Kentucky
            'LA' => 4.45,   // Louisiane
            'ME' => 5.5,    // Maine
            'MD' => 6.0,    // Maryland
            'MA' => 6.25,   // Massachusetts
            'MI' => 6.0,    // Michigan
            'MN' => 6.875,  // Minnesota
            'MS' => 7.0,    // Mississippi
            'MO' => 4.225,  // Missouri
            'MT' => 0.0,    // Montana (pas de taxe d'état)
            'NE' => 5.5,    // Nebraska
            'NV' => 6.85,   // Nevada
            'NH' => 0.0,    // New Hampshire (pas de taxe de vente)
            'NJ' => 6.625,  // New Jersey
            'NM' => 5.125,  // Nouveau-Mexique
            'NY' => 4.0,    // New York
            'NC' => 4.75,   // Caroline du Nord
            'ND' => 5.0,    // Dakota du Nord
            'OH' => 5.75,   // Ohio
            'OK' => 4.5,    // Oklahoma
            'OR' => 0.0,    // Oregon (pas de taxe d'état)
            'PA' => 6.0,    // Pennsylvanie
            'RI' => 7.0,    // Rhode Island
            'SC' => 6.0,    // Caroline du Sud
            'SD' => 4.5,    // Dakota du Sud
            'TN' => 7.0,    // Tennessee
            'TX' => 6.25,   // Texas
            'UT' => 5.95,   // Utah
            'VT' => 6.0,    // Vermont
            'VA' => 5.3,    // Virginie
            'WA' => 6.5,    // Washington
            'WV' => 6.0,    // Virginie-Occidentale
            'WI' => 5.0,    // Wisconsin
            'WY' => 4.0,    // Wyoming
            'DC' => 6.0,    // District de Columbia
        ];

        $state = strtoupper($state);
        return $usTaxRates[$state] ?? 0.0;
    }

    /**
     * @param $state
     * @param  float  $preTaxPrice
     *
     * @return float
     */
    public function calcUsSalesTax($state, float $preTaxPrice): float
    {
        $preTaxPrice = str_replace(',','',$preTaxPrice);
        return round(($this->getUsSalesTaxRate($state) * $preTaxPrice) / 100, 2);
    }
}
