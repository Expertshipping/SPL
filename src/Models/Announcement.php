<?php

namespace ExpertShipping\Spl\Models;

use Parsedown;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'announcements';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['parsed_body'];

    /**
     * Get the user that created the announcement.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the parsed body of the announcement.
     *
     * @return string
     */
    public function getParsedBodyAttribute()
    {
        return htmlspecialchars($this->attributes['body']);
    }
}
