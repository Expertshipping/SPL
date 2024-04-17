<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Models\Classes\RocketShipIt;
use ExpertShipping\Spl\Models\Mail\VoidPickUpMail;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Ramsey\Uuid\Uuid;

/**
 * @property mixed canceled_at
 */
class Pickup extends Model
{
    protected $fillable = [
        'user_id',
        'pickup_number',
        'pickup_service_code',
        'carrier_id',
        'pickup_company_name',
        'pickup_contact_name',
        'pickup_addr1',
        'pickup_addr2',
        'pickup_city',
        'pickup_state',
        'pickup_code',
        'pickup_country',
        'pickup_phone',
        'pickup_date',
        'canceled_at',
        'close_time',
        'ready_time',
        'uuid',
        'pickup_quantity',
        'accountable_type',
        'accountable_id',
        'company_id',
        'meta_data',
        'total_weight',
        'destination_country',
        'auto_created'
    ];

    protected $casts = [
        'canceled_at' => 'date',
        'pickup_date' => 'date',
        'meta_data' => 'array',
        'auto_created' => 'boolean'
    ];

    protected $with = ['carrier'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function deliveredShipments()
    {
        return $this->shipments()->where('type', 'in_progress');
    }

    public function notDeliveredShipments()
    {
        return $this->shipments()->where('type', '!=', 'delivered');
    }

    public function attachTo(Shipment $shipment)
    {
        return $this->shipments()->save($shipment);
    }

    public function void()
    {
        $rs = new RocketShipIt();
        $response = $rs->request(array(
            'carrier' => $this->carrier->carrier,
            'action' => 'CancelPickup',
            'params' => array(
                'key' => $this->carrier->key,
                'account_number' => $this->carrier->account_number,
                'username' => $this->carrier->username,
                'password' => $this->carrier->password,
                'meter_number' => $this->carrier->meter_number,
                'pickup_id' => $this->pickup_number,
                'test' => ($this->carrier->test ? true : false),
            ),
        ));
        if ($response['data']['canceled']) {
            $this->voided = true;
            $this->save();
            Mail::to(Sentinel::getUser()->email)->send(new VoidPickUpMail($this));
        }

        return $response;
    }
    /**
     * @param  \App\Shipment  $shipment
     * @param $pickup
     *
     * @return bool
     */
    public function isCancelable(): bool
    {
        if (is_null($this->tracking_number)) {
            return true;
        }

        return $this->deliveredShipments()->count() === 1;
    }

    public function isDeletable()
    {
        return !is_null($this->canceled_at) || $this->notDeliveredShipments()->count() === 0;
    }

    public function scopeFilterToScheduleShipment($query, $filterForShipment)
    {
        $query->when($filterForShipment, function ($query) {
            $query->whereNull('canceled_at')
                ->where(function ($query) {
                    $query->where('pickup_date', '>', today())
                        ->orWhere(function ($query) {
                            $query->where('pickup_date', today())
                                ->whereRaw('TIME_FORMAT(close_time,"%H:%i") > ? ', [now()->format('H:i')]);
                        });
                });
        });
    }

    public function scopeFilterByDates($query, $dates)
    {
        $query->when($dates, function ($query, $dates) {
            [$startDate, $endDate] = explode(',', $dates);
            $query->whereRaw('pickup_date between ? and ?', [$startDate, $endDate]);
        });
    }

    public function scopeFilterByStatus($query, $status)
    {
        $query->when($status, function ($query, $status) {
            $query->when($status === 'cancelled', function ($query) {
                $query->whereNotNull('canceled_at');
            });

            $query->when($status === 'active', function ($query) {
                $query->whereNull('canceled_at');
            });
        });
    }

    public function scopeFilterByCarrier($query, $carrier_id)
    {
        $query->when($carrier_id, function ($query, $carrier_id) {
            $query->where('carrier_id', $carrier_id);
        });
    }

    public function scopeFilterByPickupNumber($query, $pickup_number)
    {
        $query->when($pickup_number, function ($query, $pickup_number) {
            $query->where("pickup_number", "LIKE", "%{$pickup_number}%");
        });
    }

    public function scopeFilterByShipmentTrackingNmber($query, $shipment_tracking_number)
    {
        $query->when($shipment_tracking_number, function ($query, $shipment_tracking_number) {
            $query->whereHas('shipments', function ($q) use ($shipment_tracking_number) {
                $q->where("tracking_number", "LIKE", "%{$shipment_tracking_number}%");
            });
        });
    }

    public function accountable()
    {
        return $this->morphTo();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
