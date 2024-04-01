<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    public static function hasUserSurpassedInvoicesLimit(User $user)
    {
        $userInvoicesSum = $user->invoices()->wherePayed(false)->get()->sum('total');
        $invoiceLimit = $user->invoice_limit;
        return ($userInvoicesSum >= $invoiceLimit && $invoiceLimit != 0 ? true : false);
    }
}
