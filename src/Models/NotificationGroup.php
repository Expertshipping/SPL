<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationGroup extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get all of the adminNotification for the NotificationGroup
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function adminNotifications(): HasMany
    {
        return $this->hasMany(AdminNotification::class);
    }
}
