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
         'is_active',
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

public function getFormattedRolesAttribute()
{
    $roles = $this->user?->role ?? $this->position ?? [];
    if (!is_array($roles)) $roles = [$roles];

    $map = [
        'student_coordinator' => 'Student Coordinator',
        'adviser' => 'Adviser',
        'assessor' => 'Assessor',
        'treasurer' => 'Treasurer',
    ];

    return collect($roles)
        ->map(fn($r) => $map[$r] ?? ucfirst($r))
        ->implode(', ');
}
}
