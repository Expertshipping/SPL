<?php

namespace ExpertShipping\Spl\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use ExpertShipping\Spl\Models\City;

class CitiesService
{
    public function getCitiesByZipCodeAndCountry(Request $request)
    {
        if ($request->get('country') == 'CA') {
            return City::query()
                ->where('country', $request->get('country'))
                ->filterByCity(request('city'))
                ->filterByZipCode(request('zip_code'))
                ->orderBy('zip_code')
                ->limit(20)
                ->get();
        } else {
            $url = null;
            if (request()->has('zip_code')) {
                $url = 'https://mydhl.express.dhl/api/addressbook/search?countryCode=' . $request->get('country') .
                    '&zipCode=' . request('zip_code');
            }

            if (request()->has('city')) {
                $url = 'https://mydhl.express.dhl/api/addressbook/search?countryCode=' . $request->get('country') .
                    '&city=' . request('city');
            }
            if (!$url) {
                return [];
            }

            $res = Http::get($url);

            return collect($res->json())->map(function ($item) {
                return [
                    'country' => $item['country'],
                    'region' => $item['countryDivisionCode'] ?? '',
                    'zip_code' => $item['postalCode'] ?? '',
                    'name' => $item['city'] ?? '',
                ];
            });
        }
    }
}
