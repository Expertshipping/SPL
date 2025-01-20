<?php

namespace ExpertShipping\Spl\Actions;

use ExpertShipping\Spl\Jobs\RefundUserForAnInvoice;
use Illuminate\Support\Collection;

class InvoiceRefunded
{
    public function handle(Collection|array $models)
    {
        foreach ($models as $model) {
            if($model->paid_at) {
                dispatch(new RefundUserForAnInvoice($model));
            }
        }
    }
}
