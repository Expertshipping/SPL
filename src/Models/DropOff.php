<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Models\Traits\HasTrackingLink;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropOff extends Model
{
    use HasFactory;
    use HasTrackingLink;

    protected $fillable = [
        'id',
        'uuid',
        'company_id',
        'user_id',
        'consumer_id',
        'carrier_id',
        'scan',
        'tracking_number',
        'insurance_id',
        'phone_number',
        'email',
        'signature_name',
        'origin',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function store()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function consumer()
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class, 'carrier_id');
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class, 'insurance_id');
    }

    public function getTrackingLinkAttribute()
    {
        return str_replace(
            '{tracking_number}',
            $this->tracking_number,
            $this->carrier->tracking_link
        );
    }

    public function getGroupItemsAttribute()
    {
        return self::query()
            ->where('group_uuid', $this->group_uuid)
            ->with('carrier')
            ->get();
    }
}
