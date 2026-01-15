<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['name', 'college_id'];
    public function college() {
        return $this->belongsTo(College::class);
    }

    public function course() {
        return $this->belongsTo(Course::class);
    }

    public function yearLevel() {
        return $this->belongsTo(YearLevel::class);
    }

}
