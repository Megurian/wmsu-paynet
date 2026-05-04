<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    protected $fillable = ['name', 'college_code', 'logo'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function admins()
    {
        return $this->hasMany(User::class, 'college_id');
    }

    public function getDeanAttribute()
    {
        return $this->users->first(fn ($user) => in_array('college', (array) $user->role));
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
    public function sections()
    {
        return $this->hasMany(Section::class);
    }

}
