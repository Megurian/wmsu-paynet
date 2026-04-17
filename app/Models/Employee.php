<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'college_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'department',
        'position',
        'has_account',
    ];

    protected $casts = [
    'position' => 'array',
];
}
