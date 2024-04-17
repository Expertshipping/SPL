<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @SWG\Definition(
 *      definition="Address",
 *      required={
 *          "id",
 *          "uuid",
 *          "name",
 *      },
 *      @SWG\Property(property="id", type="integer", description="the shipping unique system id"),
 *      @SWG\Property(property="uuid", type="uuid", description="the shipping uuid"),
 * )
 */
class B2BTransaction extends JsonResource
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
            'company_name' => $this->company->name,
            'created_at' => $this->created_at->format('Y-m-d'),
            'total' => $this->total,
            'status' => $this->status,
            'product' => $this->product,
            'has_bulk' => $this->has_bulk,
            'bulk_invoices' => $this->whenLoaded('bulkInvoices'),
        ];
    }
}
