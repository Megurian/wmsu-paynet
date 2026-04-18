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
         'email',
         'user_id',
    ];

    protected $casts = [
    'position' => 'array',
];

public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function assignments()
{
    return $this->hasMany(EmployeeAssignment::class);
}

public function currentAssignment()
{
    return $this->hasOne(EmployeeAssignment::class)->latestOfMany();
}
}
