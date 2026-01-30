<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = ['name', 'org_code', 'role', 'college_id', 'logo'];
    protected $with = ['admin', 'college'];
    public function admin()
    {
        return $this->hasOne(User::class, 'organization_id')
                    ->whereIn('role', ['university_org', 'college_org']);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
