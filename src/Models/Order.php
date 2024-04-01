<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'status', 'shop_id', 'shop_number', 'total_price', 'subtotal_price', 'total_tax', 'total_weight', 'currency',
        'items', 'customer_name', 'integration_id', 'order_created_at', 'ship_to', 'shipment_id', 'meta_data', 'integration_customer_id'
    ];

    protected $casts = [
        'items' => 'array',
        'ship_to' => 'array',
        'order_created_at' => 'datetime:d-m-Y',
        'meta_data' => 'array'
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProductsAttribute()
    {
        $products_id = collect($this->items)->pluck('id')->toArray();
        return CompanyProduct::whereIn('shop_id', $products_id)->get();
    }

    private function productsSumVolume()
    {
        return collect($this->items)
            ->map(function ($item) {
                //TODO : try to findout how to improve this
                $product = $this->products->where('shop_id', $item['id'])->first();
                if (isset($product->dimensions['width']) && isset($product->dimensions['height']) && isset($product->dimensions['length'])) {
                    return [
                        'volume' => $product->dimensions['width'] * $product->dimensions['height'] * $product->dimensions['length'] * $item['quantity']
                    ];
                }
                return 0;
            })->sum('volume');
    }

    private function productsMaxWidth()
    {
        return $this->products->max('dimensions.width');
    }

    private function productsMaxHeight()
    {
        return $this->products->max('dimensions.height');
    }

    private function productsMaxLength()
    {
        return $this->products->max('dimensions.length');
    }

    public function getAdequatePackageAttribute()
    {
        $packages = collect();

        collect($this->items)
            ->each(function ($item) use (&$packages) {
                $product = auth()->user()->company->products->where('shop_id', $item['id'])->first();
                if ($product && $product->dimensions) {
                    $package = auth()->user()->company->packages
                        ->reject(function ($package) use ($product) {
                            return $package->height < $product->dimensions['height']
                                || $package->length < $product->dimensions['length']
                                || $package->width  < $product->dimensions['width'];
                        })
                        ->map(function ($package) use ($product) {
                            return [
                                'height'    => round($package->height, 2),
                                'length'    => round($package->length, 2),
                                'value'     => $package->value ? round($package->value, 2) : 0,
                                'weight'    => round($product->weight, 2),
                                'width'     => round($package->width, 2)
                            ];
                        })
                        ->sortBy('volume')
                        ->first();

                    for ($i = 0; $i < $item['quantity']; $i++) {
                        if ($package) {
                            $packages->push($package);
                        }
                    }
                }
            });

        return $packages->toArray();
    }
}
