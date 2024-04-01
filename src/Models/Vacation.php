<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Vacation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from',
        'to',
        'description',
        'approved',
        'declined',
    ];

    protected $casts = [
        'from' => 'date',
        'to' => 'date',
        'approved' => 'boolean',
        'declined' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

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

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
