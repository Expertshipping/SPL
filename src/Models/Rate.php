<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Helpers\Helper;
use ExpertShipping\Spl\Models\Nova\Actions\PartialRefund;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class Rate
{
    const CANADA_POST_ASSURANCE_VALUE = 0.04;
    /**
     * @var array
     */
    protected $rates = [];
    protected $errors = [];
    public $rateDetails = [];
    public $market_price = '-';
    public $es_price = '-';
    public $paperless = false;

    /**
     * Rate constructor.
     *
     * @param  array  $rate
     */
    private function __construct(array $rate = null)
    {
        if (!is_null($rate)) {
            $rate['logo_url'] = $this->getCarrierLogo($rate['carrier']);
            collect($rate)->each(function ($value, $key) {
                $this->{$key} = $value;
            });
        }
    }

    /**
     * @param $carrierName
     * @return String
     */
    private function getCarrierLogo($carrierName): String
    {
        if ($carrier = Carrier::where('slug', Str::lower($carrierName))->first()) {
            return $carrier->image_url;
        }

        return '';
    }

    /**
     * @param  array  $carrierRates
     * @param  Collection  $discountServices
     *
     * @return Rate
     */
    public static function normalize(array $carrierRates, Collection $discountServices)
    {
        $rates = $carrierRates['carriers'];
        $errors = $carrierRates['errors'];

        $discountsServicesLocal = $discountServices;
        $company = auth()->user()->company->load('services');
        $self = new static;
        $self->rates = collect($rates)->filter(function ($carrierRate) {
            return is_array($carrierRate['data'])
                && count($carrierRate['data']) > 0
                && is_array($carrierRate['data']['data'])
                && count($carrierRate['data']['data']) > 0
                && isset($carrierRate['data']['data']['rates']);
        })
            ->map(function ($carrier) use ($discountsServicesLocal, $company, &$self) {
                $carrierRates = $carrier['data']['data']['rates'];
                $carrierName = Str::upper($carrier['carrier']);
                $account = $carrier['data']['account'];
                return collect($carrierRates)
                    ->filter(function ($rate) use ($discountsServicesLocal, $company, $carrier) {
                        $testCompanyService = true;
                        if ($company->services->count() > 0) {
                            $testCompanyService = $company->services->containsStrict('code', $rate['service_code']);
                        }

                        return ($discountsServicesLocal->containsStrict('service_code', $rate['service_code']) || (isset($carrier['data']['account']['type']) && $carrier['data']['account']['type'] === CompanyCarrier::class))
                            && $testCompanyService;
                    })
                    ->map(function ($rate) use ($carrierName, $discountsServicesLocal, $account, &$self) {
                        $requestRate = isset($rate['total_charge']) ? $rate['total_charge']['amount'] : $rate['rate'];

                        $rate['carrier_rate'] = $requestRate;
                        $rate['rate'] = $requestRate;
                        // If it's a postmen request we add some data to the rate
                        $rate["desc"] = $rate["desc"] ?? $rate['service_name'];
                        $rate["currency"] = $rate["currency"] ?? $rate['total_charge']['currency'];
                        $rate["service_code"] = $rate["service_code"] ?? $rate['service_type'];
                        $rate["est_delivery_time"] =  $rate["est_delivery_time"] ?? $rate['delivery_date'] ?? '';

                        $rate['rate_detail'] = self::normalizeDetails($rate);

                        $rate = static::getInsuranceRate($rate, $carrierName);

                        $rate = static::getReturnLabelRate($rate);

                        if (!$rate) {
                            $search = in_array(Str::lower($carrierName), array_column($self->errors, 'carrier'));
                            if ($search === false) {
                                $self->errors[] = [
                                    'carrier' => Str::lower($carrierName),
                                    'error' => 'The maximum sum assured for this career is exceeded.'
                                ];
                            }
                            return $rate;
                        }

                        $rate["package_type"] = $rate["package_type"] ?? "";

                        if (isset($rate['negotiated_rate']) && $carrierName === 'UPS') {
                            $coast = $rate['negotiated_rate'];
                            if (isset($rate['taxes']) && count($rate['taxes']) > 0) {
                                $rate['rate'] = $coast + collect($rate['taxes'])->sum('amount');
                            } else {
                                $rate['rate'] = $coast;
                            }
                            $rate['negotiated_rate'] = $rate['rate'];
                        }

                        $rate['carrier'] = $carrierName;
                        $rate['discount_service'] = optional($discountsServicesLocal->whereStrict('service_code', $rate['service_code'])->first())->object;
                        $carrier = Carrier::whereSlug(Str::lower($carrierName))->first();
                        $rate['paperless'] = false;
                        if ($carrier)
                            $rate['paperless'] = $carrier->paperless;

                        $rate['account'] = $account;
                        return new static($rate);
                    })->reject(function ($value) {
                        return $value === false;
                    });
            })
            ->flatten(1)->filter(function ($rate) {
                return true || (float) $rate->rate > 0
                    && (
                        !cache('saturday_delivery-' . auth()->id())
                        || !!cache('saturday_delivery-' . auth()->id()) && Carbon::create($rate->est_delivery_time)->dayOfWeek === 6
                    );
            });

        $self->errors = array_merge($errors, $self->errors);

        return $self;
    }

    /**
     * @param $rate
     *
     * @return mixed
     */
    private static function normalizeDetails($rate)
    {
        if (isset($rate["detailed_charges"])) {
            return collect($rate['detailed_charges'])
                ->map(function ($detail) {
                    $detail['amount']   = $detail['charge']['amount'];
                    $detail['currency'] = $detail['charge']['currency'];
                    unset($detail['charge']);

                    return $detail;
                })->toArray();
        }
        //IN some cases the carriers doesn't send any base price we add it manually
        if ($rate['rate_detail'] === null) {
            $rate['rate_detail'] = array([
                'type' => 'BasePrice',
                'currency' => 'CAD',
                'amount' => $rate['rate']
            ]);
        }
        return $rate['rate_detail'];
    }


    public function get()
    {
        return [
            'rates' => $this->rates,
            'errors' => $this->errors,
        ];
    }

    public static function getInsuranceRate($rate, $carrier)
    {
        $insurance = Cache::get('insurance-' . auth()->id());

        // All shipments with insured value less than 105$ with free insurance
        if (!$insurance || $insurance <= 105) {
            return $rate;
        }

        $packagingType = request('packagingType');
        $boxes = collect(request('boxes'));
        $packs = collect(request('packs'));

        $calculated = true;
        if ($packagingType === "box") {
            $insuranceRate = $boxes->map(function ($box) use ($carrier, $rate, &$calculated) {
                $r = app('insurance')->getRate($box['value'], request()->from['country'], request()->to['country'], Str::lower($carrier), $rate['service_code']);
                if ($r['message'] !== 'calculated') {
                    $calculated = false;
                    return false;
                }
                return [
                    'rate' => $r['rate'],
                ];
            })->sum('rate');
        }

        if ($packagingType === "pack") {
            $insuranceRate = $packs->map(function ($pack) use ($carrier, $rate, &$calculated) {
                $r = app('insurance')->getRate($pack['value'], request()->from['country'], request()->to['country'], Str::lower($carrier), $rate['service_code']);
                if ($r['message'] !== 'calculated') {
                    $calculated = false;
                    return false;
                }
                return [
                    'rate' => $r['rate'],
                ];
            })->sum('rate');
        }

        if ($packagingType === "envelope") {
            $insuranceRate = app('insurance')->getRate($insurance, request()->from['country'], request()->to['country'], Str::lower($carrier), $rate['service_code']);
            if ($insuranceRate['message'] !== 'calculated') {
                $calculated = false;
                return false;
            }

            $insuranceRate = $insuranceRate['rate'];
        }

        if ($insuranceRate === 0 || !$calculated) {
            return false;
        }

        if (!isset($rate['rate_detail']) || !is_array($rate['rate_detail'])) {
            $rate['rate_detail'] = [];
        }
        $rate['rate_detail'][] = [
            "amount" => round($insuranceRate, 2),
            "currency" => "CAD",
            "type" => __("INSURANCE CHARGES")
        ];

        if (!isset($rate['rateDetails']) || !is_array($rate['rateDetails'])) {
            $rate['rateDetails'] = [];
        }

        $rate['rateDetails'][] = [
            "amount" => round($insuranceRate, 2),
            "currency" => "CAD",
            "type" => __("INSURANCE CHARGES")
        ];

        $rate['rate'] = Helper::formatNumber($rate['rate']) + $insuranceRate;
        if (isset($rate['negotiated_rate'])) {
            $rate['negotiated_rate'] = Helper::formatNumber($rate['negotiated_rate']) + $insuranceRate;
        }

        if (isset($rate['expert_shipping_price'])) {
            $rate['expert_shipping_price'] = Helper::formatNumber($rate['expert_shipping_price']) + $insuranceRate;
        }
        return $rate;
    }

    private static function getReturnLabelRate($rate)
    {
        if (!$rate) {
            return $rate;
        }

        $rateValue = $rate['negotiated_rate'] ?? $rate['rate'];
        if (isset($rate['taxes']) && count($rate['taxes']) > 0) {
            $rateValue += collect($rate['taxes'])->sum('amount');
        }

        if (request('returnLabel')) {
            $rate['rate_detail'][] = [
                "amount" => round($rateValue, 2),
                "currency" => "CAD",
                "type" => "Return Label"
            ];
            $rate['rate'] = round($rate['rate'] * 2, 2);

            if (isset($rate['negotiated_rate'])) {
                $rate['negotiated_rate'] = round($rate['negotiated_rate'] * 2, 2);
            }
        }

        return $rate;
    }
}
