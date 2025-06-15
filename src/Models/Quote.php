<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Quote extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'rates' => 'array',
        'package_details' => 'array',
    ];

    public static function createQuoteFromRateRequest(Request $request, $rates)
    {
        $packageDetails = self::getPackageDetails($request);
        return self::create([
            'user_id'               => $request->user()->id,
            'company_id'            => $request->user()->company_id,
            'from_zip_code'         => $request->from['zipcode'],
            'from_city'             => $request->from['city'],
            'from_country'          => $request->from['country'],
            'from_province'         => $request->from['province'],
            'to_zip_code'           => $request->to['zipcode'] ?? '',
            'to_city'               => $request->to['city'],
            'to_country'            => $request->to['country'],
            'to_province'           => $request->to['province'],
            'package_type'          => $request->packagingType,
            'package_details'       => $packageDetails,
            'insurance'             => $request->addInsurance,
            'insurance_value'       => $request->palletInsuranceValue,
            'residential'           => $request->to['residential'],
            'signature_on_delivery' => $request->to['signature_on_delivery'],
            'saturday_delivery'     => $request->to['saturday_delivery'],
            'rates'                 => $rates,
        ]);
    }

    /**
     * Get package details based on the packaging type.
     *
     * @param Request $request
     * @return array
     */
    protected static function getPackageDetails(Request $request)
    {
        return match ($request->packagingType) {
            'box' => $request->boxes,
            'pack' => $request->packs,
            'envelope' => [$request->envelopeWeight],
            'pallet' => $request->pallets,
            default => [],
        };
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }
}
