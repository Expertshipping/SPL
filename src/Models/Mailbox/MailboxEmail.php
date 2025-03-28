<?php

namespace ExpertShipping\Spl\Models\Mailbox;

use ExpertShipping\Spl\Models\Company;
use ExpertShipping\Spl\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailboxEmail extends Model
{
    use HasFactory;

    protected $connection = 'mysql_mailbox';

    protected $fillable = [
        'company_id',
        'mailbox_folder_id',
        'reply_to_id',
        'parent_id',
        'user_id',
        'email_id',
        'message_id',
        'sender_email',
        'sender_name',
        'recipient_email',
        'subject',
        'text_plain',
        'text_html_url',
        'is_seen',
        'is_answered',
        'is_recent',
        'is_flagged',
        'is_deleted',
        'is_draft',
        'cc',
        'to',
        'bcc',
        'reply_to',
        'date',
        'headers',
        'mailbox_conversation_id',
    ];

    protected $casts = [
        'is_seen' => 'boolean',
        'is_answered' => 'boolean',
        'is_recent' => 'boolean',
        'is_flagged' => 'boolean',
        'is_deleted' => 'boolean',
        'is_draft' => 'boolean',
        'cc' => 'array',
        'to' => 'array',
        'bcc' => 'array',
        'reply_to' => 'array',
        'date' => 'datetime',
        'headers' => 'array',
    ];

    public static function boot()
    {
        parent::boot();
        static::deleting(function (self $email) {
            $email->attachments()->delete();
        });
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function attachments()
    {
        return $this->hasMany(MailboxAttachment::class);
    }

    public function mailboxFolder()
    {
        return $this->belongsTo(MailboxFolder::class);
    }

    public function parent()
    {
        return $this->belongsTo(MailboxEmail::class, 'parent_id');
    }

    public function responses()
    {
        return $this->hasMany(MailboxEmail::class, 'parent_id');
    }

    public function conversation()
    {
        return $this->belongsTo(MailboxConversation::class, 'mailbox_conversation_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(MailboxEmail::class, 'reply_to_id');
    }

    public function replies()
    {
        return $this->hasMany(MailboxEmail::class, 'reply_to_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seenBy()
    {
        $database = $this->getConnection()->getDatabaseName();

        return $this->belongsToMany(User::class, "$database.mailbox_seens")
            ->using(MailboxSeen::class)
            ->withTimestamps();
    }
}
