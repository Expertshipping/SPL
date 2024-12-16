<?php


namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Helpers\Helper;
use ExpertShipping\Spl\Helpers\LabelType;
use ExpertShipping\Spl\Services\TaxService;
use Illuminate\Support\Str;

class ManageRate
{

    const DISCOUNTABLE_DETAILS = [
        'BasePrice',
        'Base Price',
        'TransportationCharges',
        'TotalBaseCharge',
        'base',
        'Freight Charge',
        'Surcharge-Fuel',
        'fuel',
        'Fuel Surcharge',
        'fuel_surcharge',
        'FUEL SURCHARGE',
        'Fuel charge',
        'Transportation Charges',
    ];

    static $FILTER_DETAILS = [
        'BasePrice',
        'TransportationCharges',
        'TotalBaseCharge',
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
        'ServiceOptionsCharges',
        'base',
        'hst',
        'gst',
        'PEHST',
        'Freight Charge',
        'pstqst',
        '120',
        'SIGNATURE_OPTION',
        'tax',
        'PSTQST',
        'VOLUME_DISCOUNT',
        'EARNED_DISCOUNT'
    ];

    static $FILTER_TAXES = [
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
    ];

    static $DETAILS_MAP = [
        'BasePrice' => 'BASE PRICE',
        //        '120' => 'SIGNATURE',
        'RESIDENTIAL_DELIVERY' => 'RESIDENTIAL DELIVERY',
        'Surcharge-Residential Delivery' => 'RESIDENTIAL DELIVERY',
        'residentialdelivery' => 'RESIDENTIAL DELIVERY',
        'Surcharge-Fuel' => 'FUEL',
        'fuel' => 'FUEL',
        'Fuel Surcharge' => 'FUEL',
        'fuel_surcharge' => 'FUEL',
        'Residential Address Charge' => 'RESIDENTIAL DELIVERY',
    ];

    const FEDEX_THIRD_PARTY = 2.6;
    const FEDEX_DSR = 3.72;

    public $destination;
    public $depart;
    public $baseRate;
    public $state;
    public $carrier;
    public $rateInfos;
    public $rateType;
    public $charge;
    public $country;
    public $zone;
    public $discountWeightIndex;
    public $discountDetailByCountryOrZoneOrWorld;

    private $rate;
    private $discountService;
    private $companyService;
    private $taxService;
    private $discountType;

    private $packagingType;

    /**
     * ManageRate constructor.
     *
     * @param $rate
     * @param $baseRate
     * @param $rateType
     * @param $rateInfos
     * @param $shipmentCountries
     * @param $state
     * @param $carrier
     * @param $discountService
     * @param $resellerCost
     * @param $packages
     * @param $weightUnit
     */
    public function __construct(
        $rate,
        $baseRate,
        $rateType,
        $rateInfos,
        $shipmentCountries,
        $state,
        $carrier,
        $discountService = null,
        $resellerCost = true,
        $packages,
        $weightUnit
    ) {
        $this->taxService = app(TaxService::class);
        $this->rate = Money::fromCurrencyAmount((float) $rate)->inCent();
        $this->baseRate = Money::fromCurrencyAmount((float) $baseRate)->inCent();
        $this->destination = $shipmentCountries['destination'];
        $this->depart = $shipmentCountries['depart'];
        $this->state = $state;
        $this->carrier = $carrier;
        $this->rateInfos = $rateInfos;
        $this->rateType = $rateType;
        $this->charge = $rate;

        if($discountService){
            $this->discountService = $discountService;
            $totalWeight = collect($packages)->sum('weight');
            $labelType = LabelType::labelType(request());
            $country = Str::upper($shipmentCountries[$labelType === 'import' ? 'depart' : 'destination']);
            $zone = null;
            if($zoneId = $this->getCarrierZoneFromCountryCode($country, $discountService->service->carrier)){
                $zone = $zoneId;
            }
            $this->zone = $zone;

            if ($weightUnit !== env('WHITE_LABEL_WEIGHT_UNIT', 'LB')) {
                if ($weightUnit === 'LB') {
                    $totalWeight = $totalWeight * 0.453592;
                }

                if ($weightUnit === 'KG') {
                    $totalWeight = $totalWeight * 2.20462;
                }
            }

            $this->discountWeightIndex = $discountService->getIndexByWeight($totalWeight, $country, $zone) ?? 'all';
            $this->companyService = $discountService;
            // define discount type (dollar or percentage) and also define discount package detail (zone or country or world)
            $this->discountDetailByCountryOrZoneOrWorld = $discountService->discount[$country][$this->discountWeightIndex] ?? $discountService->discount[$zone][$this->discountWeightIndex] ?? $discountService->discount['world'][$this->discountWeightIndex] ?? null;
            $this->defineDiscountType($discountService);
        }
    }

    public function getRate($user = null)
    {
        if (!$user) {
            $user = auth()->user();
        }
        // TODO we may need to tweak this if company member
        if ($user->company) {
            return $this->companyRate($user);
        }

        return 0;
    }

    /**
     * @return string
     */
    private function companyRate($user = null): string
    {
        $companyServiceDiscount =  $this->discountService;

        $this->companyService = $companyServiceDiscount;

        $this->defineDiscountType($companyServiceDiscount);
        if ($this->carrier === 'FEDEX') {
            $this->baseRate = $this->calcBaseRate($this->rateInfos);
        }

        if (!$companyServiceDiscount) {
            return $this->coastRate();
        }

        $packagingType = $this->packagingType ?? request()->get('packagingType');

        if ($this->discountDetailByCountryOrZoneOrWorld[$this->discountType][$packagingType] == 0) {
            return $this->coastRate();
        }

        if (!$user) {
            $user = auth()->user();
        }

        $company = $user->company;
        if ($user->account_type === 'business' && $company->carriers()->whereHas('carrier', fn ($q) => $q->where('slug', Str::slug($this->carrier)))->exists()) {
            return $this->coastRate();
        }

        if ($this->destination === 'ca' && $this->depart === 'ca') {
            return Money::fromCent(
                $this->getDiscount($companyServiceDiscount) + ($this->calcTps() + $this->calcTvp())
            )->inCurrencyAmount();
        }

        if ($this->destination === 'ma' && $this->depart === 'ma') {
            return Money::fromCent(
                $this->getDiscount($companyServiceDiscount) + $this->calcTva(20)
            )->inCurrencyAmount();
        }

        return Money::fromCent($this->getDiscount($companyServiceDiscount))->inCurrencyAmount();
    }

    /**
     * @return string
     */
    private function coastRate(): string
    {
        return Money::fromCent($this->rate)->inCurrencyAmount();
    }

    /**
     * @param $service
     *
     * @return float|int
     */
    private function getDiscount($service)
    {
        $discountValue = $this->discountValue($service);
        if ((float) $discountValue < 0) {
            return $this->calcRate() - $this->calculateDiscountValue(abs($discountValue), $this->discountType);
        }

        return $this->calcRate() + $this->calculateDiscountValue($discountValue, $this->discountType);
    }

    public function calcRate()
    {
        if ($this->destination === 'ca' && $this->depart === 'ca') {
            $tax = $this->provinceTaxDetails();
            return $this->rate - ($tax['tpsAmount'] + $tax['tvpAmount']);
        }

        return $this->rate;
    }

    public function calcBaseRate(array $details)
    {
        $rate = $this->calcRate();
        $totalCharges = $this->totalCharges($details);
        return $rate - Money::fromCurrencyAmount($totalCharges)->inCent();
    }
    /**
     * @param $discount
     *
     * @param $type
     *
     * @return float|int
     */
    private function calculateDiscountValue($discount, $type)
    {
        //        if($this->rateType === 'full_rate') {
        //            return 0;
        //        }

        if ($type === 'percentage') {
            return ($this->rate * $discount) / 100;
        }

        return Money::fromCurrencyAmount((float) $discount)->inCent();
    }

    private function calculateDiscountValueForAmount($amount, $discount, $type)
    {
        if ($type === 'percentage') {
            return ($amount * $discount) / 100;
        }

        return $discount;
    }

    /**
     * @param $service
     */
    private function discountValue($service)
    {
        $packagingType = $this->packagingType ?? request()->get('packagingType');

        return $this->discountDetailByCountryOrZoneOrWorld[$this->discountType][$packagingType];
    }

    private function provinceTaxDetails()
    {
        return app(TaxService::class)->details(
            Money::fromCent($this->rate)->inCurrencyAmount(),
            $this->state
        );
    }

    public function rateDetails(array $details, $fromCountry, $toCountry)
    {
        $allDetails = collect(array_merge($details))
            ->filter()
            ->filter($this->filterTaxes())
            ->map(function ($detail) {
                $amount = str_replace(' ', '', $detail['amount']);
                $amount = str_replace(',', '', $amount);
                $link = null;

                if ($this->discountType === 'link_to_carrier') {
                    $amount = -1;
                    $link = $this->discountDetailByCountryOrZoneOrWorld;
                } else {
                    if (Helper::inArrayWithoutCase($detail['type'], self::DISCOUNTABLE_DETAILS)) {
                        if ((float) $this->discountValue($this->companyService) < 0) {
                            $amount = $amount - $this->calculateDiscountValueForAmount($amount, abs($this->discountValue($this->companyService)), $this->discountType);
                        } else {
                            $amount = $amount + $this->calculateDiscountValueForAmount($amount, abs($this->discountValue($this->companyService)), $this->discountType);
                        }
                    }
                }

                return [
                    'type' => $detail['type'],
                    'currency' => $detail['currency'],
                    'amount' => round($amount, 2),
                    'link' => $link
                ];
            });

        $taxes = $this->addTaxes($allDetails, $fromCountry, $toCountry);

        return  $allDetails->merge($taxes)
            ->map($this->normalizeDetails())
            ->map($this->mapDetails())
            ->toArray();
    }

    private function addTaxes($details, $fromCountry, $toCountry)
    {
        $taxes = collect();
        $totalCharges = $details->sum('amount');

        if ($fromCountry === 'CA' && $toCountry === 'CA') {
            if (in_array($this->state, ['NB', 'NL', 'NS', 'ON', 'PE'])) {
                $taxes->push([
                    'type' => 'tps-plus-tvp',
                    'currency' => 'CAD',
                    'amount' => Money::fromCent(($this->calcTpsForAmount($totalCharges) + $this->calcTvpForAmount($totalCharges)) * 100)->inCurrencyAmount()
                ]);
            } else {
                $taxes->push([
                    'type' => 'TPS',
                    'currency' => 'CAD',
                    'amount' => Money::fromCent($this->calcTpsForAmount($totalCharges) * 100)->inCurrencyAmount()
                ]);

                $taxes->push([
                    'type' => 'TVP',
                    'currency' => 'CAD',
                    'amount' => Money::fromCent($this->calcTvpForAmount($totalCharges) * 100)->inCurrencyAmount()
                ]);
            }
        }

        if ($fromCountry === 'MA' && $toCountry === 'MA') {
            $taxes->push([
                'type' => 'TVA',
                'currency' => 'MAD',
                'amount' => Money::fromCent($this->calcTvaForAmounnt(20, $totalCharges) * 100)->inCurrencyAmount()
            ]);
        }

        return $taxes;
    }

    /**
     *
     * @return string
     */
    private function calcTps()
    {
        return $this->taxService->tps($this->state) * $this->getDiscount($this->companyService) / 100;
    }

    /**
     *
     * @return string
     */
    private function calcTpsForAmount($amount)
    {
        return $this->taxService->tps($this->state) * $amount / 100;
    }

    /**
     *
     * @return string
     */
    private function calcTvp()
    {
        return $this->taxService->tvp($this->state) * $this->getDiscount($this->companyService) / 100;
    }

    /**
     *
     * @return string
     */
    private function calcTvpForAmount($amount)
    {
        return $this->taxService->tvp($this->state) * $amount / 100;
    }

    /**
     *
     * @return string
     */
    private function calcTva($tva)
    {
        return $tva * $this->getDiscount($this->companyService) / 100;
    }

    /**
     *
     * @return string
     */
    private function calcTvaForAmounnt($tva, $amount)
    {
        return $tva * $amount / 100;
    }

    private function filterDetails()
    {
        return  function ($detail) {
            static::$FILTER_DETAILS[] = $this->state . 'HST';
            static::$FILTER_DETAILS[] = $this->state . 'GST';
            return $detail['amount'] > 0
                && !in_array(
                    $detail['type'],
                    static::$FILTER_DETAILS
                );
        };
    }

    private function filterTaxes()
    {
        return  function ($detail) {
            static::$FILTER_TAXES[] = $this->state . 'HST';
            static::$FILTER_TAXES[] = $this->state . 'GST';
            return $detail['amount'] > 0
                && !in_array(
                    $detail['type'],
                    static::$FILTER_TAXES
                );
        };
    }

    /**
     * @return \Closure
     */
    private function mapDetails(): \Closure
    {
        return function ($item) {
            $item['type'] = isset(static::$DETAILS_MAP[$item['type']])
                ? Str::upper(static::$DETAILS_MAP[$item['type']])
                : $item['type'];

            if (in_array($this->state, ['NB', 'NL', 'NS', 'ON', 'PE'])) {
                if ($item['type'] === 'tps-plus-tvp') {
                    $item['type'] = 'HST';
                }
            } else {
                if ($item['type'] === 'TPS') {
                    $item['type'] = 'GST';
                }
                if ($item['type'] === 'TVP') {
                    $item['type'] = $this->state === 'QC' ? 'QST' : 'PST';
                }
            }
            return $item;
        };
    }

    /**
     * @return \Closure
     */
    private function normalizeDetails(): \Closure
    {
        return function ($item) {
            $item['type'] = (function ($str) {
                $formattedStr = '';
                $re           =
                    '/(?<=[a-z])(?=[A-Z])| (?<=[A-Z])(?=[A-Z][a-z])/x';
                $a            = preg_split($re, $str);
                $formattedStr = implode(' ', $a);

                return $formattedStr;
            })($item['type']);

            return $item;
        };
    }

    /**
     * @param $companyServiceDiscount
     */
    private function defineDiscountType($companyServiceDiscount): void
    {
        $xDollar = 0;
        $xPercentage = 0;
        $rateExample = (float) Money::fromCent($this->rate)->inCurrencyAmount();
        $packagingType = request()->get('packagingType');

        if (isset($this->discountDetailByCountryOrZoneOrWorld['link_to_carrier_id'])) {
            $this->discountType = 'link_to_carrier';
            return;
        }

        $discountValue = $this->discountDetailByCountryOrZoneOrWorld['dollar'][$packagingType];
        if (!is_null($discountValue) && $discountValue != "") {
            $xDollar = $rateExample + $discountValue;
        }
        $discountValue = (float) $this->discountDetailByCountryOrZoneOrWorld['percentage'][$packagingType];
        if (!is_null($discountValue) && $discountValue != "") {
            $xPercentage = $rateExample + ($rateExample * $discountValue) / 100;
        }
        $this->discountType = $xDollar > $xPercentage ? 'dollar' : 'percentage';
    }

    /**
     * @param  array  $details
     */
    private function totalCharges(array $details)
    {
        return collect($details)
            ->filter()
            ->filter($this->filterDetails())
            ->sum('amount');
    }

    public static function getInstance($fromCountry, $toCountry, $toState)
    {
        return new static(
            0,
            0,
            'full_rate',
            [],
            [
                'depart' => $fromCountry,
                'destination' => $toCountry
            ],
            $toState,
            null,
            null,
            true,
            [],
            null
        );
    }

    public function recalculateTaxes($returnRates){
        return $returnRates->map(function($rate){
            $rateDetails = collect($rate->rateDetails)
                ->filter($this->filterTaxes());
            $taxes = $this->addTaxes($rateDetails, $this->depart, $this->destination);
            $rate->rateDetails = $rateDetails->merge($taxes)
                ->map($this->normalizeDetails())
                ->map($this->mapDetails())
                ->toArray();

            $totalDetails = collect($rate->rateDetails)->sum('amount');
            $rate->carrier_price = $this->formatPrice($totalDetails);
            $rate->expert_shipping_price = $this->formatPrice($totalDetails);

            return $rate;
        });
    }

    private function formatPrice($price)
    {
        if(is_int($price)){
            $price = sprintf("%.2f", $price);
        }

        $price = floatval(str_replace(",", "", $price));
        return number_format($price, 2);
    }

    private function getCarrierZoneFromCountryCode($countryCode, $carrier): ?string
    {
        $country = Country::where('code', $countryCode)->first();
        if($country){
            $labelType = LabelType::labelType(request());
            if($zone = $carrier
                ->zones()
                ->whereHas('countries', function($q) use ($country){
                    $q->where('countries.id', $country->id);
                })
                ->where('import_or_export', 'like', "%$labelType%")
                ->first()
            ){
                return "{$zone->id}";
            }
        }
        return null;
    }

    public function setPackagingType($packagingType)
    {
        $this->packagingType = $packagingType;
    }
}
