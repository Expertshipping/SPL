<?php

namespace ExpertShipping\Spl\Resources;

use ExpertShipping\Spl\Models\Carrier;
use ExpertShipping\Spl\Models\File;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

/**
 * @SWG\Definition(
 *      definition="Shipment",
 *      required={
 *          "id",
 *          "uuid",
 *          "name",
 *      },
 *      @SWG\Property(property="id", type="integer", description="the shipping unique system id"),
 *      @SWG\Property(property="uuid", type="uuid", description="the shipping uuid"),
 * )
 */
class Shipment extends JsonResource
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
            'description' => $this->description,
            'factured' => $this->factured,
            'pickedup' => $this->pickedup,
            'voided' => $this->voided,
            'residential' => $this->residential,
            'tracking_number' => $this->is_paid ? $this->tracking_number : "********",
            'tracking_link' => $this->is_paid ? $this->when(
                $this->resource->relationLoaded('carrier'),
                $this->trackingLink($this->tracking_number, optional($this->carrier)->tracking_link)
            ) : null,
            'model' => class_basename($this),
            'status' => $this->type,
            'status_display' => \App\Shipment::STATUSES[$this->type] ?? $this->type,
            'rate' => $this->rate,
            'from_name' => $this->from_name,
            'from_company' => $this->from_company,
            'from_address' => $this->from_address_1,
            'from_address_two' => $this->from_address_2,
            'from_address_three' => $this->from_address_3,
            'from_zip_code' => $this->from_zip_code,
            'from_city' => $this->from_city,
            'from_country' => $this->from_country,
            'from_province' => $this->from_province,
            'from_phone' => $this->from_phone,
            'from_email' => $this->from_email,
            'reference_value' => $this->reference_value,

            'to_name' => $this->to_name,
            'to_company' => $this->to_company,
            'to_address' => $this->to_address_1,
            'to_address_two' => $this->to_address_2,
            'to_address_three' => $this->to_address_3,
            'to_zip_code' => $this->to_zip_code,
            'to_city' => $this->to_city,
            'to_country' => $this->to_country,
            'to_province' => $this->to_province,
            'to_phone' => $this->to_phone,
            'to_email' => $this->to_email,
            'terms_of_sale' => $this->terms_of_sale,
            'package_content' => $this->package_content,
            'document_type' => $this->document_type,
            'shipping_purpose' => $this->shipping_purpose,
            'b13_number' => $this->b13_number,
            'commodity_description' => $this->commodity_description,
            'hs_no' => $this->hs_no,
            'country_of_manufacture' => $this->country_of_manufacture,
            'service_code' => $this->service_code,
            'cpf_cnpj' => $this->cpf_cnpj,
            'return_label' => $this->return_label,
            'original_shipment' => $this->when(
                $this->returnLabel,
                new Shipment(optional($this->returnLabel)->load(['carrier','invoice']))
            ),
            'carrier' => new CarrierResource($this->whenLoaded('carrier')),
            'user' => $this->whenLoaded('user'),
            'package' => new PackageResource($this->whenLoaded('package')),
            'pickup' => $this->whenLoaded('pickup'),
            'invoice' => $this->whenLoaded('invoice'),
            'custom_invoice' => $this->is_paid ? $this->whenLoaded('customInvoice') : null,
            'service' => $this->whenLoaded('service'),
            'label_url' => !is_null($this->label_url) ? explode(',', $this->label_url) : [],
            'start_date' => !! $this->start_date ? $this->start_date->format('Y-m-d'): null,
            'created_at' => $this->deliveryDate($this->created_at),
            'created_hour' => $this->created_at->format('H:i'),
            'updated_at' => $this->updated_at,
            'comments' => new CommentCollection($this->whenLoaded('comments')),
            'insurances' => $this->whenLoaded('insurances'),
            'files' => $this->files(),
            'manifest' => $this->whenLoaded('manifest'),
            'has_issue' => $this->has_issue,
            'issue_description' => $this->issue_description,
            'total_charged' => round($this->total_charged, 2),
            'consumer_invoice_link' => $this->saleDetail && $this->saleDetail->invoice && $this->saleDetail->invoice->token ? url('/i/'.$this->saleDetail->invoice->token) : '',
            'saturday_delivery' => $this->saturday_delivery,
            'signature_on_delivery' => $this->signature_on_delivery,
            'is_paid' => $this->is_paid,
            'aramex_bulk' => $this->whenLoaded('aramexBulk', new Shipment(optional($this->aramexBulk))),
            'estimated_delivery_date' => $this->estimated_delivery_date ? $this->estimated_delivery_date->format('Y-m-d') : null,
            'delivered_at' => $this->delivered_at ? $this->delivered_at->format('Y-m-d H:i') : null,
            'picked_up_at' => $this->picked_up_at ? $this->picked_up_at->format('Y-m-d H:i') : null,
            'estimated_delivery_days' => $this->estimated_delivery_date ? $this->estimated_delivery_date->diffInDays($this->created_at) : null,
            'pickup_days' => $this->picked_up_at ? $this->picked_up_at->diffInDays($this->created_at) : null,
            'delivery_days' => $this->delivered_at ? $this->delivered_at->diffInDays($this->created_at) : null,
            'invoice_detail' => $this->whenLoaded('saleDetail'),
            'pos_receipt' => $this->whenLoaded('receiptDetail'),
            'orders' => new OrderCollection($this->whenLoaded('orders')),
            'company' => $this->whenLoaded('company'),
            'notifications' => new ShipmentNotificationCollection($this->whenLoaded('notifications')),
        ];
    }

    /**
     * @param  Carbon  $delivery
     *
     * @return string
     */
    private function deliveryDate(Carbon $delivery): string
    {
        return $delivery->toFormattedDateString();
    }

    protected function files(){
        $countriesFiles = Cache::remember('users', 500, function () {
            return File::all();
        });

        return $countriesFiles
            ->where('country_code', $this->resource->to_country)
            ->map(function($file){
                return [
                    'name' => $file->name,
                    'url' => $file->type==="url"?$file->url:$file->getMedia('attachment')[0]->getUrl(),
                ];
            })->toArray();
    }
}
