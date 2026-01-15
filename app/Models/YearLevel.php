<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YearLevel extends Model
{
    protected $fillable = ['name', 'college_id'];
    public function college() {
        return $this->belongsTo(College::class);
    }

    public function sections() {
        return $this->hasMany(Section::class);
    }

}
