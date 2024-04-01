<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class City extends Model
{
    public function scopeFilterByCity($query, $term = null)
    {
        $query->when($term, function ($query, $term) {
            collect(str_getcsv($term, ' ', '"'))->filter()->each(function ($term) use ($query) {
                $term = $term . '%';
                $query->where('name', 'like', $term);
            });
        });
    }

    public function scopeFilterByZipCode($query, $zipCodeTerm = null)
    {
        $query->when($zipCodeTerm, function ($query, $zipCodeTerm) {
            $zipCodeTerm = (string) Str::of($zipCodeTerm)->trim();
            collect(str_getcsv($zipCodeTerm, ' ', '"'))->filter()->each(function ($zipCodeTerm) use ($query) {
                $zipCodeTerm = $zipCodeTerm . '%';
                $query->where('zip_code', 'like', $zipCodeTerm);
            });
        });
    }
}
