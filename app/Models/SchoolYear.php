<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    use HasFactory;

    protected $fillable = ['sy_start', 'sy_end', 'is_active'];

    public function semesters() {
        return $this->hasMany(Semester::class);
    }

    public function activeSemester() {
        return $this->hasOne(Semester::class)->where('is_active', true);
    }
}
