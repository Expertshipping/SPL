<?php

namespace ExpertShipping\Spl\Resources;

use ExpertShipping\Spl\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopRateResource extends JsonResource
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
            } catch (\Throwable $th) {
                $delivery = now()->addDays(3);
            }

            $rateVisibility = auth()->user()->loadMissing('company')->rateVisibility();
            $rate = $this->isDiscountRateDisplayable($rateVisibility) ? $this->expert_shipping_price:$this->carrier_price;
            $rate = str_replace(',', '', $rate);
            $service = Service::where('code', $this->service_code)->first();

            return  [
                        'service_name'  => $service->name,
                        'service_code'  => $service->carrier_id."_".$service->id,
                        'total_price'   => round($rate * 100, 2),
                        'description'   => !! $this->desc? $this->desc:$this->service_code,
                        'currency'      => "CAD",
                        'min_delivery_date' => $delivery->format('Y-m-d H:i:s') ." -0400",
                        'max_delivery_date' => $delivery->format('Y-m-d H:i:s') ." -0400",
                    ];
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
}
