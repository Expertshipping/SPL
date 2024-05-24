<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Models\Retail\Warning;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class AdminNotification extends Model
{
    use HasFactory;
    use HasTranslations;

    const EVENTS_LIST = [
        [
            'value' => 'App\Events\Retail\WarningGivenToAgent',
            'label' => 'Warning Given To Agent',
            'model' => Warning::class,
        ]
    ];

    protected $translatable = [
        'email_subject',
        'email_content',
        'sms_content',
        'app_notification_content',
        'app_notification_title',
        'web_notification_content',
        'acknowledgment_content',
    ];

    protected $guarded = [];

    protected $casts = [
        'channels' => 'array',
        'scheduled_at' => 'datetime',
        'require_acknowledgment' => 'boolean',
        'email_subject' => 'array',
        'email_content' => 'array',
        'sms_content' => 'array',
        'app_notification_content' => 'array',
        'web_notification_content' => 'array',
        'acknowledgment_content' => 'array',
        'stores' => 'array',
        'agents' => 'array',
        'frequency_days' => 'array',
        'frequency_time' => 'datetime:H:i',
        'frequency_one_time_sent' => 'boolean',
    ];

    public function scopeScheduled($query)
    {
        return $query->where('type', 'scheduled');
    }

    public function scopeEvent($query)
    {
        return $query->where('type', 'event');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function acknowledgments()
    {
        return $this->hasMany(Acknowledgment::class);
    }

    public function scopeStores($query, $stores)
    {
        return $query->whereJsonContains('stores', $stores);
    }

    public function scopeAgents($query, $agents)
    {
        return $query->whereJsonContains('agents', $agents);
    }

    public static function getNotifiables()
    {
        $notifiables = [];
        foreach (self::EVENTS_LIST as $event) {
            $notifiables[$event['value']] = $event['model']::query()->get()->map(function ($model) {
                return [
                    'value' => $model->id,
                    'label' => $model->name,
                ];
            });
        }

        return $notifiables;
    }

    public function notifiable()
    {
        return $this->morphTo();
    }
}
