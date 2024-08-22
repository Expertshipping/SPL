<?php

namespace ExpertShipping\Spl\Models\Mailbox;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailboxFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
    ];

    protected $connection = 'mysql_mailbox';
}
