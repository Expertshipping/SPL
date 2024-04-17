<?php

namespace ExpertShipping\Spl\Resources;

use ExpertShipping\Spl\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class Inventory extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_name' => $this->user->name,
            'store_name' => $this->company->name,
            'created_at' => $this->created_at->format("d M Y"),
            'questions' => $this->questions,
            'products' => $this->getProductsFromInvenotry($this->products),
            'carrier_supplies' => $this->carrier_supplies,
            'reception' => $this->reception,
            'status' => $this->status,
            'tracking_number' => $this->tracking_number,
            'source' => $this->source,
        ];
    }

    private function getProductsFromInvenotry($products){
        $productsModels = Product::whereIn('id', collect($products)->pluck('id'))->with('category')->withTrashed()->get();
        return collect($products)->map(function($product) use ($productsModels){
            if(isset($product['id'])){
                $productModel = $productsModels->where('id', $product['id'])->first();
                return [
                    'id'            => $productModel->id,
                    'category_image'=> $productModel->category->image_url,
                    'category_name' => $productModel->category->name,
                    'category_id'   => $productModel->category->id,
                    'name'          => $productModel->name,
                    'response'      => $product['response']??null,
                    'stock'         => $product['stock']??0,
                    'stock_alert'   => $product['stock_alert']??false,
                    'page'          => $productModel->category->inventory_report_page_number,
                    'managed_stock' => $productModel->managed_stock
                ];
            }else{
                return [
                    'name'          => $product['name'],
                    'carriers'      => $product['carriers'] ?? null,
                    'stock'         => $product['stock'] ?? 0,
                    'stock_alert'   => $product['stock_alert'] ?? false,
                ];
            }
        });
    }

}
