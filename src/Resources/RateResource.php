<?php

namespace ExpertShipping\Spl\Resources;

use ExpertShipping\Spl\Models\CarrierPickup;
use ExpertShipping\Spl\Models\Helpers\Helper;
use ExpertShipping\Spl\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use ExpertShipping\Spl\Models\Spark;

class RateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        try {
            $delivery = new Carbon($this->est_delivery_time);
            $transitDays = ((integer) $this->deliveryDate($delivery) !== 0 ? $this->deliveryDate($delivery): 1)." days";
        } catch (\Throwable $th) {
            $transitDays = $this->delivery_days;
            $delivery = $this->deliveryDateFromDeliveryText($transitDays);
        }

        $rateVisibility = auth()->user()->loadMissing('company')->rateVisibility();

        $paperless = false;
        if (isset(auth()->user()->options['printing_options']['paperless']) && auth()->user()->options['printing_options']['paperless'] === true) {
            $paperless = $this->paperless ?? false;
        }

        $account = $this->account;
        if(isset($account['api_credentials'])){
            unset($account['api_credentials']);
        }

        if (auth()->user()->account_type==="retail") {
            $dailyPickup = CarrierPickup::isCompleted(optional(Service::where('code', $this->service_code)->first())->carrier_id, $this->service_code==='FEDEX_GROUND');
        }else{
            $dailyPickup = false;
        }

        $totalWeight = $request->units=='imperial' ? 1 : 0.5;
        $totalVolumetricWeight = 0;
        if($request->packagingType=='pack'){
            $packs = collect($request->packs);
            $totalWeight = $request->units=='imperial' ? ($packs->sum('weight')*0.453592) : $packs->sum('weight');
            $totalVolumetricWeight = 0;
        }

        if($request->packagingType=='box'){
            $boxes = collect($request->boxes);
            $totalWeight = $request->units=='imperial' ? ($boxes->sum('weight')*0.453592) : $boxes->sum('weight');
            $totalVolumetricWeight = Helper::calculateVolumetricWeightKG(['packages' => $boxes->toArray(), 'weight_unit' => $request->units=='imperial' ? 'LB' : 'KG']);
        }

        return  [
                'desc' => !! $this->desc? $this->desc:$this->service_code,
                'carrier' => $this->carrier,
                'service' => $this->service_code,
                'transit_days' => $transitDays,
                'carrier_price' => $this->when($this->isFullRateDisplayable($rateVisibility), $this->carrier_price ?? $this->carrier_rate),
                'expert_shipping_price' => $this->when($this->isDiscountRateDisplayable($rateVisibility), $this->expert_shipping_price ?? $this->carrier_rate),
                'logo_url' => $this->logo_url,
                'rate_details' => !!$this->rateDetails ? $this->rateDetails: [],
                'paperless' => $paperless,
                'is_international' => $request->from['country'] !== $request->to['country'],
                'account' => $account,
                'user_rate' => isset($this->user_rate)?(new RateResource($this->user_rate)):null,
                // Todo: get charge from freightcom
                'charge' => auth()->user()->company->is_retail_reseller || in_array(auth()->user()->email, Spark::$adminDevelopers) ? round(str_replace(",", "", $this->charge ?? 0), 2) : 0,
                'chargeable_weight' => round(max($totalWeight, $totalVolumetricWeight), 2),
                'chargeable_method' => $totalWeight<$totalVolumetricWeight ? 'volumetric' : 'Actual Weight',
                'cheapest' => $this->cheapest ?? false,
                'fastest' => $this->fastest ?? false,
                'bestvalue' => $this->bestvalue ?? false,
                'estimated_delivery_date' => $delivery->format('Y-m-d'),
                'daily_pickup' => $dailyPickup,
                'market_price' => $this->market_price,
                'es_price' => $this->es_price,
                'f_code' => $this->freightcom_code ?? '-',
            ];
    }

    /**
     * @param Carbon $delivery
     *
     * @return string|null
     */
    private function deliveryDate(Carbon $delivery): ?string
    {
        try {
            $shipDate = new Carbon(request()->get('startDate'));
            return $delivery->diffInDays($shipDate);
        } catch (\Throwable $th) {
            return null;
        }
    }

    /**
     * @param $rateVisibility
     *
     * @return bool
     */
    private function isFullRateDisplayable($rateVisibility): bool
    {
        return  $rateVisibility && $rateVisibility['full_rate'];
    }

    /**
     * @param $rateVisibility
     *
     * @return bool
     */
    private function isDiscountRateDisplayable($rateVisibility): bool
    {
        return $rateVisibility['discount_rate'];
    }

    /**
     * @param $transitDays
     *
     * @return Carbon
     */
    private function deliveryDateFromDeliveryText($transitDays): Carbon
    {
        $pattern = '/\d+/';
        if (preg_match($pattern, $transitDays, $matches)) {
            $days = $matches[0];
        } else {
            $days = 1;
        }

        $delivery = new Carbon(request()->get('startDate'));
        $delivery = $delivery->addDays($days);

        return $delivery;
    }
}
