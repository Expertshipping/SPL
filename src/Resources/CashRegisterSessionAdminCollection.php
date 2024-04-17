<?php

namespace ExpertShipping\Spl\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CashRegisterSessionAdminCollection extends ResourceCollection
{

    protected $totals;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $totals)
    {
        parent::__construct($resource);
        $this->resource = $this->collectResource($resource);
        $this->totals = $totals;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'totals' => $this->totals
        ];
    }
}
