<?php

namespace ExpertShipping\Spl\Models;

use App\Services\SmsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleBusiness extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function company()
    {
        return $this->belongsTo(Company::class)
            ->whereHas('user', function ($q) {
                $q->where('account_type', 'retail');
            });
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function sendSms($phone)
    {
        $numbers = explode(',', $phone);
        $smsService = app(SmsService::class);
        foreach ($numbers as $number) {
            $smsService->send($number, $this->sms_message);
        }
    }

    public function storeLocator()
    {
        return $this->hasOne(StoreLocator::class);
    }
}
