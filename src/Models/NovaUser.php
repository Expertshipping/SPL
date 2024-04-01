<?php

namespace ExpertShipping\Spl\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class NovaUser extends User
{
    use HasFactory;

    protected $table = "users";
}
