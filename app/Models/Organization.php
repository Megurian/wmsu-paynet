<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = ['name', 'org_code', 'role', 'college_id', 'logo'];

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function admin()
    {
        return $this->hasOne(User::class)->whereIn('role', ['college_org', 'university_org']);
    }
}
