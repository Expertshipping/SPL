<?php

namespace ExpertShipping\Spl\Utilities;

interface FilterContract
{
    public static function apply($query, $value);
}
