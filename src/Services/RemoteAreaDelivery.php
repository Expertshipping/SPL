<?php

namespace ExpertShipping\Spl\Services;

use ExpertShipping\Spl\Models\RemoteArea;
use Illuminate\Support\Facades\Http;

class RemoteAreaDelivery
{
    protected $url = "https://api.trackingmore.com/v3/trackings/remote";
    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Tracking-Api-Key' => 'ik5bmxpm-g6ao-75ju-3c8f-hv2yp950jyxb',
        ]);
    }

    public function check($country, $zipCode)
    {
        $remoteArea = RemoteArea::query()
            ->where('country', $country)
            ->where('postal_code', $zipCode)
            ->first();

        if ($remoteArea) {
            return $remoteArea->remote_courier_code;
        }
        $response = $this->httpClient->post($this->url, [
            'country' => $country,
            'postal_code' => $zipCode,
        ])->json();
        $remoteCourierCode = $response['data']['remote_courier_code'] ?? null;
        if ($remoteCourierCode) {
            RemoteArea::create([
                'country' => $country,
                'postal_code' => $zipCode,
                'remote_courier_code' => $remoteCourierCode,
            ]);
        }
        return $remoteCourierCode;
    }
}
