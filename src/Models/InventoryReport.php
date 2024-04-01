<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class InventoryReport extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'questions' => 'array',
        'products' => 'array',
        'managed_stock_products' => 'array',
        'alert' => 'boolean',
        'carrier_supplies' => 'boolean',
        'reception' => 'json',
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function orderProductsByCategories($type): array
    {
        $categories = CategoryProduct::whereNull('parent_id')
            ->whereNull('company_id')
            ->with(['childrens'])
            ->get();

        $result = [];
        $productsDb = $this->addNameToProducts($this->{$type});
        foreach ($categories as $category) {
            $products = $productsDb
                ->filter(function ($product) use ($category) {
                    return $product['category_id'] === $category->id;
                });

            $countProducts = $products->count();

            $childrens = collect();
            if ($category->childrens->count() > 0) {
                foreach ($category->childrens as $child) {
                    $childProducts = $productsDb
                        ->filter(function ($product) use ($child) {
                            return $product['category_id'] === $child->id;
                        });

                    $countProducts += $childProducts->count();

                    $childrens->push([
                        'id' => $child->id,
                        'name' => $child->name,
                        'products' => $childProducts,
                    ]);
                }
            }
            if ($countProducts) {
                $result[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'products' => $products,
                    'childrens' => $childrens
                ];
            }
        }

        return $result;
    }

    /**
     * @param $products
     * @return \Illuminate\Support\Collection
     */
    private function addNameToProducts($products): \Illuminate\Support\Collection
    {
        return collect($products)->map(function ($product) {
            $productObject = Product::find($product['id']);
            return [
                "id" => $product['id'],
                "name" => $productObject->name,
                "category_id" => $productObject->category_product_id,
                "stock" => $product['stock'] ?? null,
                "response" => $product['response'],
                "managed_stock" => $product['managed_stock'],
                "minimum_required" => $product['minimum_required'] ?? null,
            ];
        });
    }

    public function parent()
    {
        return $this->belongsTo(InventoryReport::class, 'parent_id');
    }

    public function childrens()
    {
        return $this->hasMany(InventoryReport::class, 'parent_id');
    }

    public static function carrierSupplies()
    {
        $carriers = Carrier::query()
            ->active()
            ->notLtl()
            ->activeForInventory()
            ->get()->map(function ($carrier) {
                return [
                    'id' => $carrier->id,
                    'name' => $carrier->name,
                    'slug' => $carrier->slug,
                    'value' => false,
                ];
            });

        return [
            ['name' => 'Boite moyenne', 'carriers' => $carriers,],
            ['name' => 'Boite grande', 'carriers' => $carriers,],
            ['name' => 'Pack', 'carriers' => $carriers,],
            ['name' => 'Enveloppe', 'carriers' => $carriers,],
            ['name' => 'Labels (rouleau)', 'carriers' => $carriers,],
            ['name' => 'Labelope', 'carriers' => $carriers,],
        ];
    }

    public function allProductsReceived()
    {
        $products = $this->products;
        $productsReceived = $this->reception ?? [];
        if ($this->carrier_supplies) {
            $productsReceived = collect($productsReceived)->map(function ($product) {
                return [
                    'id' => $product['name'],
                    'quantity' => collect($product['response'])->where('value', true)->count(),
                ];
            });

            $products = collect($products)->map(function ($product) {
                return [
                    'id' => $product['name'],
                    'quantity' => count($product['carriers']),
                ];
            });

            return $products->sum('quantity') === $productsReceived->sum('quantity');
        } else {
            // get received products with a response only
            $productsReceived = collect($productsReceived)
                ->filter(function ($product) {
                    return isset($product['response']);
                })
                ->pluck('id')->toArray();
            $products = collect($products)->pluck('id')->toArray();

            return $productsReceived === $products;
        }
    }
}
