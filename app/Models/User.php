<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'email',
        'password',
        'role',
        'college_id',
        'organization_id',
        'course_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'array',
        ];
    }
    public function getFullNameAttribute(): string
    {
        return trim(
            "{$this->first_name} {$this->middle_name} " .
            ($this->last_name ?? '') . ' ' .
            ($this->suffix ?? '')
        );
    }
    public function college()
    {
        return $this->belongsTo(College::class);
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

   public function hasRole($role)
{
    return in_array($role, $this->role ?? []);
}

public function isAssessor(): bool
{
    return in_array('assessor', $this->role ?? []);
}

public function isStudentCoordinator(): bool
{
    return in_array('student_coordinator', $this->role ?? []);
}

public function isCollege(): bool
{
    return in_array('college', $this->role ?? []);
}
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

public function getRoleAttribute($value)
{
    $decoded = json_decode($value, true);

    return is_array($decoded) ? $decoded : [$value];
}

public function getRoleLabelAttribute(): string
{
    return collect($this->role ?? [])
        ->map(fn($r) => ucwords(str_replace('_', ' ', $r)))
        ->join(', ');
}
}
