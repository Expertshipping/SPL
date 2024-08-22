<?php

namespace ExpertShipping\Spl\Models\Mailbox;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailboxAttachment extends Model
{
    use HasFactory;

    protected $connection = 'mysql_mailbox';

    protected $fillable = [
        'mailbox_email_id',
        'name',
        'path',
        'size',
        'extension',
        'mime_type',
        'content_id',
    ];

    public function email()
    {
        return $this->belongsTo(MailboxEmail::class, 'mailbox_email_id');
    }

}
