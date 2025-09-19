<?php

namespace ExpertShipping\Spl\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Twilio\Rest\Client;

class SmsService
{
    private $client, $accountSID, $authToken, $twilioNumber;

    public function __construct()
    {
        $this->accountSID = config("twilio.sid");
        $this->authToken = config("twilio.auth_token");
        $this->twilioNumber = config("twilio.number");
        if(request()->user()) {
            $credentials = request()->user()->company->sms_credential ?? [];
            if(
                !empty($credentials['twilio_sid']) &&
                !empty($credentials['twilio_number']) &&
                !empty($credentials['twilio_auth_token']))
            {
                $this->accountSID = $credentials['twilio_sid'];
                $this->authToken = $credentials['twilio_number'];
                $this->twilioNumber = $credentials['twilio_auth_token'];
            }
        }
        $this->client = new Client($this->accountSID, $this->authToken);
    }

    public function send($to, $message){
        // before sending the message, check if the environment is local
        if(config('app.env') == 'local'){
            Log::info("SMS sent to $to: $message");
            return;
        }

        // before sending the message, check if the phone number is valid
        $validPhone = $this->client->lookups->v2->phoneNumbers($to)
            ->fetch(["type" => ["carrier"]]);

        if($validPhone->valid) {
            try {
                $this->client->messages->create($to, [
                    'from' => $this->twilioNumber,
                    'body' => $message,
                ]);
            } catch (\Throwable $th) {
                throw $th;
            }
        }else{
            throw ValidationException::withMessages(['phone' => __('Invalid phone number')]);
        }
    }

    public function sendMMS($to, $message, $from=null ,$mediaUrl=[]){
        if(config('app.env') == 'local'){
            Log::info("SMS sent to $to: $message");
            return;
        }
        try {
            $this->client->messages->create($to, [
                'from' => $from || $this->twilioNumber,
                'body' => $message,
                "mediaUrl" => $mediaUrl
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
