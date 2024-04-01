<?php

namespace ExpertShipping\Spl\Models;

use App\Jobs\CreatePickupWithDeliveryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AutomatedPickup extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'day' => 'array'
    ];

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createPickup()
    {
        $carrier = $this->carrier;

        if ($carrier && $carrier->slug === 'ups' && $service = Service::find($this->pickup_service_code)) {
            $service = "0" . $service->code;
        } else if ($carrier && $carrier->slug === 'fedex') {
            $service = $this->pickup_service_code;
        } else {
            $service = null;
        }

        $date = now()->format('Y-m-d');
        if (
            $carrier && $carrier->slug === 'fedex'
            && $this->pickup_service_code === 'fedex_ground' &&
            in_array(Str::lower(date('l')), ['friday', 'saturday', 'sunday'])
        ) {
            $date = now()->addWeek()->startOfWeek()->format('Y-m-d');
        }

        $pickup = Pickup::create([
            'carrier_id' => $this->carrier_id,
            'pickup_service_code' => $service,
            'destination_country' => $this->destination_country,
            'total_weight' => $this->total_weight,
            'pickup_company_name' => $this->pickup_company_name,
            'pickup_contact_name' => $this->pickup_contact_name,
            'pickup_addr1' => $this->pickup_addr1,
            'pickup_city' => $this->pickup_city,
            'pickup_state' => $this->pickup_state,
            'pickup_code' => $this->pickup_code,
            'pickup_country' => $this->pickup_country,
            'pickup_phone' => $this->pickup_phone,
            'pickup_date' => $date,
            'close_time' => $this->close_time,
            'ready_time' => $this->ready_time,
            'pickup_quantity' => $this->pickup_quantity,
            'user_id' => $this->user_id,
            'company_id' => $this->company_id,
            'auto_created' => true,
        ]);

        dispatch(new CreatePickupWithDeliveryService($pickup));
    }
}
