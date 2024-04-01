<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TodoList extends Model
{
    use HasFactory;

    protected $casts = [
        'for_all_stores' => 'boolean',
        'for_half_time_sessions' => 'boolean'
    ];

    public function stores()
    {
        return $this->belongsToMany(Company::class, 'todo_list_company', 'todo_list_id', 'company_id')
            ->using(TodoListCompany::class)
            ->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(TodoListTask::class);
    }
}
