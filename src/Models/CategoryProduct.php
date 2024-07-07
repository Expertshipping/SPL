<?php

namespace ExpertShipping\Spl\Models;

use ExpertShipping\Spl\Resources\ProductCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class CategoryProduct extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;
    use HasTranslations;

    protected $guarded = [];
    protected $translatable = ['name'];

    // protected $appends = ['image_url', 'title'];

    protected $casts = [
        'consommable' => 'boolean',
        'inventoriable' => 'boolean',
    ];

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('categories-images');
    }

    public function getTitleAttribute()
    {
        if ($this->parent) {
            return $this->parent->name . " >> " . $this->name;
        } else {
            return $this->name;
        }
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function hide()
    {
        HiddenCategories::create([
            'category_product_id' => $this->id,
            'company_id' => auth()->user()->company_id,
            'manager_id' => auth()->id(),
        ]);
    }

    public function parent()
    {
        return $this->belongsTo(CategoryProduct::class, 'parent_id');
    }

    public function childrens()
    {
        return $this->hasMany(CategoryProduct::class, 'parent_id')->orderBy('name');
    }

    public static function pos()
    {
        $categories = self::where('consommable', false)
            ->whereNull('parent_id')
            ->with('products', function ($q) {
                $q->where('consommable', false);
                $q->when(auth()->user()->company->is_retail_reseller, function ($query) {
                    $query->where('company_id', auth()->user()->company_id);
                });

                $q->whereNotIn('id', auth()->user()->company->hiddenProducts->pluck('id'));
                $q->where(function ($query) {
                    $query->where('company_id', auth()->user()->company_id)
                        ->orWhereNull('company_id');
                });
                $q->with('media', 'inventories');
            })
            ->whereNotIn('id', auth()->user()->company->hiddenCategories->pluck('id'))
            ->where(function ($query) {
                $query->where('company_id', auth()->user()->company_id)->orWhereNull('company_id');
            })
            ->with([
                'childrens.products' => function ($q) {
                    $q->where('consommable', false);
                    // $q->where('hide_from_pos', false);
                    $q->when(auth()->user()->company->is_retail_reseller, function ($query) {
                        $query->where('company_id', auth()->user()->company_id);
                    });

                    $q->whereNotIn('id', auth()->user()->company->hiddenProducts->pluck('id'));
                    $q->where(function ($query) {
                        $query->where('company_id', auth()->user()->company_id)
                            ->orWhereNull('company_id');
                    });
                    $q->with('media', 'inventories');
                }
            ])
            ->with('media')
            ->get();

        $order = auth()->user()->company->categories_order;
        return $categories->map(function ($category) use ($order) {
            return self::categoryResource($category, $order);
        });
    }

    private static function categoryResource($category, $order)
    {
        if (isset($order[$category->id])) {
            $ids = $order[$category->id];
            $products = $category->products->sortBy(function ($model) use ($ids) {
                return array_search($model->getKey(), $ids);
            })->values();
        } else {
            $products = $category->products;
        }

        $childrens = $category->childrens->map(function ($category) use ($order) {
            return self::categoryResource($category, $order);
        });

        return [
            "id"            => $category->id,
            "name"          => $category->name,
            "name_origin"   => $category->getTranslations('name'),
            "created_at"    => $category->created_at,
            "updated_at"    => $category->updated_at,
            "color"         => $category->color,
            "consommable"   => $category->consommable,
            "inventoriable" => $category->inventoriable,
            "inventory_report_page_number"   => $category->inventory_report_page_number,
            "products"      => (new ProductCollection($products))->collection,
            "childrens"     => $childrens,
        ];
    }

    public function setImageAttribute()
    {
    }
}
