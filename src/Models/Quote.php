<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Models\Http\Requests\RatesRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_zip_code',
        'from_city',
        'from_country',
        'from_province',
        'to_zip_code',
        'to_city',
        'to_country',
        'to_province',
        'package_type'
    ];


    public static function createQuoteFromRateRequest(Request $request)
    {
        return self::create([
            'user_id'           => $request->user()->id,
            'from_zip_code'     => $request->from['zipcode'],
            'from_city'         => $request->from['city'],
            'from_country'      => $request->from['country'],
            'from_province'     => $request->from['province'],
            'to_zip_code'       => $request->to['zipcode'] ?? '',
            'to_city'           => $request->to['city'],
            'to_country'        => $request->to['country'],
            'to_province'       => $request->to['province'],
            'package_type'      => $request->packagingType
        ]);
    }
}
