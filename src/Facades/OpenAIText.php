<?php

namespace ExpertShipping\Spl\Facades;

use Illuminate\Support\Facades\Facade;

class OpenAIText extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'openai-text';
    }
}
