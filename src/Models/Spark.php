<?php

namespace ExpertShipping\Spl\Models;

use App\Traits\SparkConfiguration\ManagesAppDetails;
use App\Traits\SparkConfiguration\ManagesAppOptions;

class Spark
{

    use ManagesAppDetails;
    use ManagesAppOptions;

    public static $adminDevelopers = [
        'm.alaoui@expertshipping.ca',
        'c.mallette@expertshipping.ca',
        'mouadaarab@gmail.com',
        'accounting@expertshipping.ca',
        'm.alaoui@shippayless.com',
        'm.alaoui@awsel.ma',
        'h.alaoui@awsel.ma',
    ];

    public static function getTranslations()
    {
        $translationFile = resource_path('lang/' . app()->getLocale() . '.json');

        if (!is_readable($translationFile)) {
            $translationFile = resource_path('lang/' . app('translator')->getFallback() . '.json');
        }

        return json_decode(file_get_contents($translationFile), true);
    }
}
