<?php

namespace ExpertShipping\Spl\Models\Mailbox;

use ExpertShipping\Spl\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailboxConversation extends Model
{
    use HasFactory;

    protected $connection = 'mysql_mailbox';

    protected $fillable = [
        'company_id',
        'subject',
        'last_email_date',
    ];

    protected $casts = [
        'last_email_date' => 'datetime',
    ];

    public function emails()
    {
        return $this->hasMany(MailboxEmail::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
