<?php

namespace ExpertShipping\Spl\Helpers;

use ExpertShipping\Spl\Models\Carrier;
use ExpertShipping\Spl\Models\EmailPreference;
use Illuminate\Support\Str;

class Helper
{
    public static function moneyFormat($amount, $currency="$")
    {
        $amount = number_format(round($amount, 2), 2);
        $amount = str_replace(',','',$amount);

        try {
            if($amount<0){
                return "- $currency".abs($amount);
            }else{
                return "$currency$amount";
            }
        } catch (\Throwable $th) {
            return "$currency$amount";
        }
    }

    public static function convertToHoursMins($time, $format = '%02d:%02d') {
        if ($time < 1) {
            return;
        }
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        return sprintf($format, $hours, $minutes);
    }

    public static function emailPreference($company, $notificationClass) {
        // Email preferences only for b2b accounts
        if($company->users->where('account_type','retail')->count()>0){
            return true;
        }

        if($emailPreference = EmailPreference::where('notification_class', $notificationClass)->first()){
            $companyPreferencesIds = $company->emailPreferences->pluck('id')->toArray();
            return in_array($emailPreference->id, $companyPreferencesIds);
        }
        return false;
    }

    public static function calculateVolumetricWeightKG($payload){
        $packages = collect($payload['packages']);
        $totalWeight = 0;
        $formula2 = 2.54;
        foreach ($packages as $package) {
            if(isset($package['weight']) && isset($package['length']) && isset($package['width']) && isset($package['height'])){
                if($payload['weight_unit'] === 'LB'){
                    $length = round($package['length']*$formula2, 2);
                    $width = round($package['width']*$formula2, 2);
                    $height = round($package['height']*$formula2, 2);
                    $totalWeight += (($width*$length*$height)/5000);
                }else{
                    $length = $package['length'];
                    $width = $package['width'];
                    $height = $package['height'];
                    $totalWeight += (($width*$length*$height)/6000);
                }
            }
        }

        return $totalWeight;
    }


    /**
     * @param $length
     * @param $width
     * @param $height
     * @param $carrier
     * @param $unit
     * @return float|int
     */
    public static function calculateVolumetricWeightByUnit($length, $width, $height, $carrier, $unit, $serviceTransportType = 'airway'): float|int
    {
        $weight = $width * $length * $height;
        $carriers = [
            'fedex' => [
                'airway_lb' => $weight/166,
                'airway_kg' => $weight/6000,
                'ground_lb' => $weight/166,
                'ground_kg' => $weight/6000,
            ],
            'dhl' => [
                'airway_lb' => $weight/139,
                'airway_kg' => $weight/5000,
                'ground_lb' => $weight/166,
                'ground_kg' => $weight/6000,
            ],
            'ups' => [
                'airway_lb' => $weight/139,
                'airway_kg' => $weight/5000,
                'ground_lb' => $weight/166,
                'ground_kg' => $weight/6000,
            ],
            'purolator' => [
                'airway_lb' => $weight/1728*15,
                'airway_kg' => $weight/28316*15,
                'ground_lb' => $weight/1728*10,
                'ground_kg' => $weight/28316*10,
            ],
            'canpar' => [
                'airway_lb' => $weight/166,
                'airway_kg' => $weight/6000,
                'ground_lb' => $weight/166,
                'ground_kg' => $weight/6000,
            ],
            'gls' => [
                'airway_lb' => $weight/139,
                'airway_kg' => $weight/5000,
                'ground_lb' => $weight/166,
                'ground_kg' => $weight/6000,
            ],
            'aramex' => [
                'airway_lb' => $weight/139,
                'airway_kg' => $weight/5000,
                'ground_lb' => $weight/166,
                'ground_kg' => $weight/6000,
            ],
        ];

        $default = [
            'airway_lb' => $weight/139,
            'airway_kg' => $weight/5000,
            'ground_lb' => $weight/139,
            'ground_kg' => $weight/5000,
        ];

        $name = Str::lower($serviceTransportType.'_'.$unit);


        return $carriers[$carrier][$name] ?? $default[$name];
    }

    public static function calculateVolumetricWeight($packages, Carrier $carrier, $unit){
        $totalWeight = 0;
        foreach ($packages as $package) {
            if(!is_array($package)){
                $package = json_decode(json_encode($package), true);
            }

            if (isset($package['weight']) && isset($package['length']) && isset($package['width']) && isset($package['height'])) {
                $totalWeight += self::calculateVolumetricWeightByUnit($package['length'], $package['width'], $package['height'], $carrier->slug, $unit);
            }
        }

        return $totalWeight;
    }

    public static function distanceInKm($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;
        return $distance;
    }



    // - A package with the longest side exceeding 48 inches (122 centimetres) or second-longest side exceeding 30 inches (76 centimetres)
    // - Each domestic or export package in a shipment where the average weight per package is greater than 50 pounds/22 kilograms or each import package in a shipment where the average weight per package is greater than 70 pounds/32 kilograms and the weight for each package is not specified on the source document or the UPS automated shipping system used.
    // - A package that is not fully encased in corrugated cardboard. Any cylindrical-like item, such as a barrel, drum, bucket or tire, that is not fully encased in a corrugated cardboard shipping container.

    public static function requiresAdditionalHandling($weight, $length, $width, $height, $importOrExport, $unit = 'metric') {
        if ($unit === 'metric') {
            $weightLimitImport = 32;
            $weightLimitExport = 22;
            $weightLimitDomestic = 22;
            $longestSideLimit = 122;
            $secondLongestSideLimit = 76;
        } else {
            $weightLimitImport = 70;
            $weightLimitExport = 50;
            $weightLimitDomestic = 50;
            $longestSideLimit = 48;
            $secondLongestSideLimit = 30;
        }

        $sides = [$length, $width, $height];
        sort($sides);
        $longestSide = $sides[2];
        $secondLongestSide = $sides[1];

        if (
            $longestSide > $longestSideLimit ||
            $secondLongestSide > $secondLongestSideLimit ||
            ($importOrExport == 'import' && $weight > $weightLimitImport) ||
            ($importOrExport == 'export' && $weight > $weightLimitExport) ||
            ($importOrExport == 'local' && $weight > $weightLimitDomestic)
        ) {
            return true;
        }
        return false;
    }

    public static function isSpecified($weight) {
        // Check if the weight is specified
        // You can implement this function as per your requirement
        return true;
    }

    public  static function isFullyEncased($weight, $length, $width, $height) {
        // Check if the package is fully encased in corrugated cardboard
        // You can implement this function as per your requirement
        return true;
    }

    // A Package is considered a Large Package when its length plus girth [(2 x width) + (2 x height)] combined exceeds 130 inches (330 centimetres) or when a domestic Package length exceeds 96 inches (244 centimetres). Large Packages are subject to a minimum billable weight of 90 pounds (41 kilograms). An additional charge, set forth in the UPS Rates applicable to the Shipment in effect at the time of shipping, will also be applied to a Large Package.

    public static function calculateUpsLargePackageSurcharge($weight, $length, $width, $height, $unit) {
        if ($unit === 'imperial') {
            $girth = $width*2 + $height * 2;
            return $girth + $length > 130 || $length > 96;
        } else if ($unit === 'metric') {
            $girth = $width*2 + $height * 2;
            return $girth + $length > 330 || $length > 244;
        }

        return false;
    }

    public static function convertCurrency($amount, $fromCurrency){
        $toCurrency = request()->platformCountry?->currency ?? 'CAD';
        // $url = "https://api.exchangeratesapi.io/latest?base={$fromCurrency}&symbols={$toCurrency}";
        // $response = Http::
        //     withHeaders([
        //         'Accept' => 'application/json',
        //     ])
        //     ->get($url);
        // dd($response->json());

        if($toCurrency=='CAD' && $fromCurrency=='USD'){
            return round($amount * 1.32, 2);
        }

        if($toCurrency=='CAD' && $fromCurrency=='EUR'){
            return round($amount * 1.53, 2);
        }

        if($toCurrency=='CAD' && $fromCurrency=='GBP'){
            return round($amount * 1.77, 2);
        }

        if($toCurrency=='CAD' && $fromCurrency=='AUD'){
            return round($amount * 0.92, 2);
        }

        if($toCurrency=='CAD' && $fromCurrency=='MAD'){
            return round($amount * 0.12, 2);
        }

        if($toCurrency=='MAD'){
            return round($amount * 11, 2);
        }

        return $amount;
    }

    public static function inArrayWithoutCase($needle, $haystack) {
        $needle = self::removeSpaceAndLowerCase($needle);
        return in_array(strtolower($needle), array_map(self::removeSpaceAndLowerCase(...), $haystack));
    }

    public static function removeSpaceAndLowerCase($string) {
        return strtolower(str_replace(' ', '', $string));
    }

}
