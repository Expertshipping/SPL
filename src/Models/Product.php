<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;
    use HasTranslations;

    protected $translatable = ['name'];

    protected $guarded = [];

    protected $with = ['category'];

    protected $casts = [
        'taxable' => 'boolean',
        'consommable' => 'boolean',
        'variable' => 'boolean',
        'managed_stock' => 'boolean',
        'hide_from_pos' => 'boolean',
        'stockable' => 'boolean',
        'tracking_number' => 'boolean',
    ];

    // protected $appends = ['on_hand_inventory', 'inventory_status','image_url'];

    public function getImageUrlAttribute()
    {
        return $this->getFirstMediaUrl('products-images');
    }

    public function category()
    {
        return $this->belongsTo(CategoryProduct::class, 'category_product_id');
    }

    public function getInventoryStatusAttribute()
    {

        if ($this->stockable && $this->minimum_required && $inventory = $this->getInventory()) {
            if ($inventory->on_hand_inventory === 0) {
                return "OUT_OF_STOCK";
            }

            if ($inventory->on_hand_inventory <= $this->minimum_required) {
                return "ALERT_STOCK";
            }

            return "AVAILABLE";
        }

        return "NA";
    }

    public function getInventory($company = null)
    {
        if (!$company) {
            $company = auth()->user()->company;
        }

        if ($company && $inventory = $this->inventories->where('company_id', $company->id)->first()) {
            return $inventory;
        }

        return null;
    }

    public function getOnHandInventoryAttribute()
    {
        if ($inventory = $this->getInventory()) {
            return $inventory->on_hand_inventory;
        }
        return null;
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function hide()
    {
        HiddenProduct::create([
            'product_id' => $this->id,
            'company_id' => auth()->user()->company_id,
            'manager_id' => auth()->id(),
        ]);
    }

    public function scopeShowInPOS($query)
    {
        return $query->where('hide_from_pos', 0);
    }

    public function scopeHideInPOS($query)
    {
        return $query->where('hide_from_pos', 1);
    }
}
