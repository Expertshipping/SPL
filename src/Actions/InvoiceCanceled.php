<?php

namespace ExpertShipping\Spl\Actions;

use ExpertShipping\Spl\Jobs\SetInvoiceAsCancelled;
use Illuminate\Support\Collection;

class InvoiceCanceled
{
    public function handle(Collection|array $models)
    {
        foreach ($models as $model) {
            dispatch_sync(new SetInvoiceAsCancelled($model));
        }
    }
}
