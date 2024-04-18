<?php

namespace ExpertShipping\Spl\Services;

use ExpertShipping\Spl\Models\Insurance;
use Illuminate\Support\Facades\Http;

class InsuranceService
{

    private $http;

    public function __construct()
    {
        $this->http = Http::withBasicAuth(config('spl.insurance.username'), config('spl.insurance.password'))
            ->baseUrl(config('spl.insurance.base_uri'));
    }

    public function voidTransactionForInsurance(Insurance $insurance)
    {
        $params = [
            'userId' => config('spl.insurance.user_id'),
            'trackingNum' => $insurance->tracking_number,
        ];
        $response = $this->http->get('TransactionService.svc/VoidTrans', $params);
        if($response->status()===200 && $response->json()['VoidTransResult']['Result']==="Success"){
            $insurance->update([
                'status' => 'voided'
            ]);
            return true;
        }

        return false;
    }

}
