<?php

namespace ExpertShipping\Spl\Models\Traits;

use ExpertShipping\Spl\Utilities\FilterBuilder;

trait Filterable
{
    public function scopeFilter($query, $filters)
    {
        return FilterBuilder::for($query, $filters);
    }

    // Support only two levels of nested relationships
    public function scopeFilterRelation($query, $filters, $relationship)
    {
        return FilterBuilder::for($query, $filters, $relationship);
    }
}
