<?php

namespace ExpertShipping\Spl\Facades;

use Illuminate\Support\Facades\Facade;

class SplMoney extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'SPL.money';
    }
}
