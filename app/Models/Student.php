<?php

namespace App\Models;

use App\Notifications\StudentResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'student_id',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'contact',
        'email',
        'religion',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    // Returns the student's current enrollment record
    public function currentEnrollment()
    {
        // Try to get the enrollment for the active school year/semester
        return $this->hasOne(StudentEnrollment::class)
            ->whereHas('schoolYear', function ($query) {
                $query->where('is_active', true);
            })
            ->whereHas('semester', function ($query) {
                $query->where('is_active', true);
            });
    }

    // Returns the most recent enrollment record
    public function latestEnrollment()
    {
        return $this->hasOne(StudentEnrollment::class)->latestOfMany();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new StudentResetPasswordNotification($token));
    }
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}" . ($this->suffix ? " {$this->suffix}" : ''));
    }
}
