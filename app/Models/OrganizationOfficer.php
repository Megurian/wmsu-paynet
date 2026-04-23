<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationOfficer extends Model
{
    protected $table = 'organization_officers';

    protected $fillable = [
        'student_id',
        'organization_id',
        'role',
        'semester_id',
        'is_active',
        'user_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}