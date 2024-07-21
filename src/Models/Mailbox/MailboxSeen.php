<?php

namespace ExpertShipping\Spl\Models\Mailbox;

use ExpertShipping\Spl\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MailboxSeen extends Pivot
{
    use HasFactory;

    protected $connection = 'mysql_mailbox';

    protected $table = 'mailbox_seens';

    protected $fillable = [
        'mailbox_email_id',
        'user_id'
    ];

    public function mailboxEmail()
    {
        return $this->belongsTo(MailboxEmail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
