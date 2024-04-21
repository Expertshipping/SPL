<?php

namespace ExpertShipping\Spl\Resources;

use ExpertShipping\Spl\Models\Carrier;
use ExpertShipping\Spl\Models\File;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

/**
 * @SWG\Definition(
 *      definition="Shipment",
 *      required={
 *          "id",
 *          "uuid",
 *          "name",
 *      },
 *      @SWG\Property(property="id", type="integer", description="the shipping unique system id"),
 *      @SWG\Property(property="uuid", type="uuid", description="the shipping uuid"),
 * )
 */
class ShipmentNotification extends JsonResource
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
        return [
            'id'                => $this->id,
            'created_at'        => $this->created_at->format('d/m/Y H:i:s'),
            'humain_created_at' => $this->created_at->diffForHumans(),
            'notification'      => $this->notification,
            'client_user_id'    => $this->client_user_id,
            'admin_user_id'     => $this->admin_user_id,
            'admin'             => $this->whenLoaded('adminUser'),
            'client'            => $this->whenLoaded('clientUser')
        ];
    }

    /**
     * @param  Carbon  $delivery
     *
     * @return string
     */
    private function deliveryDate(Carbon $delivery): string
    {
        return $delivery->toFormattedDateString();
    }

    protected function files(){
        $countriesFiles = Cache::remember('users', 500, function () {
            return File::all();
        });

        return $countriesFiles
            ->where('country_code', $this->resource->to_country)
            ->map(function($file){
                return [
                    'name' => $file->name,
                    'url' => $file->type==="url"?$file->url:$file->getMedia('attachment')[0]->getUrl(),
                ];
            })->toArray();
    }
}
