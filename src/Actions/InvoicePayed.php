<?php

namespace ExpertShipping\Spl\Actions;

use ExpertShipping\Spl\Jobs\SetInvoiceAsPaid;
use Illuminate\Support\Collection;

class InvoicePayed
{
    public function handle(Collection|array $models)
    {
        foreach ($models as $model) {
            dispatch_sync(new SetInvoiceAsPaid($model));
        }
    }

}
