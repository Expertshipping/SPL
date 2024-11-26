<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class SupportCategory extends Model
{
    use HasFactory;

    use HasTranslations;

    public $translatable = ['name'];

    public function supportContents()
    {
        return $this->hasMany(SupportContent::class);
    }
}
