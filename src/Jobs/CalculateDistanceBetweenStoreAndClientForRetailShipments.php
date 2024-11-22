<?php

namespace ExpertShipping\Spl\Jobs;

use ExpertShipping\Spl\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CalculateDistanceBetweenStoreAndClientForRetailShipments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Shipment $shipment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $from = $this->shipment->from_address_1 . ' ' . $this->shipment->from_city . ' ' . $this->shipment->from_province . ' ' . $this->shipment->from_zip_code . " CANADA";
        $to = $this->shipment->company->addr1 . ' ' . $this->shipment->company->city . ' ' . $this->shipment->company->state . ' ' . $this->shipment->company->zip_code . " CANADA";

        $from = urlencode($from);
        $to = urlencode($to);

        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$from&destinations=$to&key=" . config('services.google.api_places_key');
        $response = Http::get($url)->json();

        $this->shipment->distance_details = $response;
        $this->shipment->save();
    }
}
