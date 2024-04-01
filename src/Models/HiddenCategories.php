<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class HiddenCategories extends Pivot
{
    use HasFactory;

    protected $table = "hidden_categories";
    protected $guarded = [];
}
