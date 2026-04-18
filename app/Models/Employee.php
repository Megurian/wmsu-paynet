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
         'user_id',
    ];

    protected $casts = [
    'position' => 'array',
];

public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
}
