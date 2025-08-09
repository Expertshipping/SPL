<?php

namespace ExpertShipping\Spl\Models;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    //    use Searchable;

    public function toSearchableArray()
    {
        $array = $this->toArray();

        $data = [
            'id' => $array['id'],
            'full_name' => $array['full_name'],
            'company' => $array['company'],
            'phone' => $array['phone']
        ];

        return $data;
    }

    protected $fillable = [
        'full_name', 'addr1', 'addr2', 'addr3', 'city', 'code',
        'state', 'country', 'discretionary_notes', 'user_id',
        'phone', 'company', 'email', 'uuid', 'company_id'
    ];
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Uuid::uuid4();
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }


    public function scopeFilterBySearchTerm($query, $term)
    {
        $query->where(function ($query) use ($term) {
            $query->when($term, function ($query, $term) {
                collect(str_getcsv($term, ' ', '"'))->filter()->each(function ($term) use ($query) {
                    $term = $term . "%";
                    $query->where('company', 'like', $term)
                        ->orWhere('full_name', 'like', $term)
                        ->orWhere('addr1', 'like', $term)
                        ->orWhere('addr2', 'like', $term)
                        ->orWhere('state', 'like', $term);
                });
            });
        });
    }
}
