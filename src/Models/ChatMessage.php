<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'sender_important' => 'boolean',
        'recipient_important' => 'boolean',
        'seen' => 'boolean',
        'attachment' => 'array',
        'important_details' => 'array',
        'group_seen_details' => 'array'
    ];


    public function sender()
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'to_id');
    }

    public function group()
    {
        return $this->belongsTo(ChatGroup::class, 'to_id');
    }

    public function whoAmI($user)
    {
        if ($user->id === $this->from_id) {
            return 'sender';
        } else {
            return 'recipient';
        }
    }

    public function respondsTo()
    {
        return $this->belongsTo(self::class, 'responds_id');
    }

    public function getImportantAttribute()
    {
        if ($this->type === 'user') {
            return $this->{$this->whoAmI(auth()->user()) . "_important"};
        }

        return in_array(auth()->id(), $this->important_details ?? []);
    }

    public function getSeenAttribute($value)
    {
        if ($this->type === 'user') {
            return $value;
        }

        return $this->group_seen_details && count($this->group_seen_details) === $this->group->members->count() - 1;
    }
}
