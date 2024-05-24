<?php

namespace ExpertShipping\Spl\Services;

use ExpertShipping\Spl\Helpers\Helper;
use ExpertShipping\Spl\Models\User;

class UserIpAddress
{
    public static function getUserIp()
    {
        return request()->header('x-vapor-source-ip') ?? request()->ip();
    }

    public static function checkIfUserInStore(User $user)
    {
        if (!request()->has('lat') || !request()->has('lng')) {
            return false;
        }

        $agentPositionLat = request()->get('lat');
        $agentPositionLng = request()->get('lng');
        $agentPositionLat = floatval($agentPositionLat);
        $agentPositionLng = floatval($agentPositionLng);

        $company = $user->company;
        $companyPositionLat = $company->lat_lng['lat'];
        $companyPositionLng = $company->lat_lng['lng'];
        $companyPositionLat = floatval($companyPositionLat);
        $companyPositionLng = floatval($companyPositionLng);

        $distance = Helper::distanceInKm($agentPositionLat, $agentPositionLng, $companyPositionLat, $companyPositionLng);
        return $distance < 0.2;
    }
}
