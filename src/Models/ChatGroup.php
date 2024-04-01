<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatGroup extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'pinned' => 'boolean',
    ];

    public function members()
    {
        return $this->belongsToMany(User::class, 'chat_group_members', 'group_id', 'user_id')
            ->using(ChatGroupMember::class)
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'to_id');
    }

    public function getPhotoAttribute($value)
    {
        return empty($value) ? self::defaultPhoto() : url($value);
    }

    public static function defaultPhoto()
    {
        return asset('assets/chat/gens.png');
    }
}
