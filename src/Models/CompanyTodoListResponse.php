<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyTodoListResponse extends Pivot
{
    protected $guarded = [];

    protected $casts = [
        'tasks' => 'array',
        'confirmed' => 'boolean'
    ];
}
