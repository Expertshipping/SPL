<?php

return [
    'middleware'        => env('SPL_MIDDLEWARE', []),
    'response_type'     => env('SPL_RESPONSE_TYPE', 'web'),


    'insurance' => [
        'base_uri'  => env('INSURANCE_API_URI', 'https://api.ecabrella.com/'),
        'username'  => env('INSURANCE_API_USERNAME', 'expert$249Sp'),
        'password'  => env('INSURANCE_API_PASSWORD', '$D316#ex45Ship7si'),
        'user_id'  => env('INSURANCE_API_USER_ID', 'ExpertShipping'),
    ],
];
