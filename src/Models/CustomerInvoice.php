<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerInvoice extends Model
{
    protected $fillable = [
        'shipment_id',
        'exportation_reason',
        'exportation_type',
        'terms_of_sale',
        'vat_tax_id',
        'items',
        'bill_to_name',
        'bill_to_company',
        'bill_to_address',
        'bill_to_address_2',
        'bill_to_address_3',
        'bill_to_zip_code',
        'bill_to_city',
        'bill_to_country',
        'bill_to_province',
        'bill_to_tel',
        'bill_to_email',
        'currency'
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function getItemsAttribute($items)
    {
        return collect(json_decode($items))->map(function ($item) {
            (float) $item->value /= 100;

            return $item;
        })->toArray();
    }
}
