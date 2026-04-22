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
        'invitation_sent_at',
        'invitation_accepted_at',
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
    protected $casts = [
        'email_verified_at' => 'datetime',
        'invitation_sent_at' => 'datetime',
        'invitation_accepted_at' => 'datetime',
        'password' => 'hashed',
        'role' => 'array',
    ];

    public function getInvitationStatusAttribute(): ?string
    {
        if ($this->invitation_accepted_at) {
            return 'active';
        }

        if (! $this->invitation_sent_at) {
            return null;
        }

        return $this->invitation_sent_at->addMinutes((int) config('auth.passwords.users.expire'))->isPast()
            ? 'expired'
            : 'pending';
    }

    public function getInvitationExpiresAtAttribute()
    {
        return $this->invitation_sent_at
            ? $this->invitation_sent_at->addMinutes((int) config('auth.passwords.users.expire'))
            : null;
    }

    public function getInvitationPendingAttribute(): bool
    {
        return $this->invitationStatus === 'pending';
    }

    public function getInvitationExpiredAttribute(): bool
    {
        return $this->invitationStatus === 'expired';
    }

    public function getInvitationActiveAttribute(): bool
    {
        return $this->invitationStatus === 'active';
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

    public function setRoleAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['role'] = json_encode($value);
            return;
        }

        if ($value === null) {
            $this->attributes['role'] = null;
            return;
        }

        if (is_string($value)) {
            // If the stored string is already JSON, preserve it.
            $decoded = json_decode($value, true);
            $this->attributes['role'] = $decoded === null ? json_encode($value) : $value;
            return;
        }

        $this->attributes['role'] = $value;
    }

    public function getRoleAttribute($value)
    {
        if ($value === null) {
            return [];
        }

        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if ($decoded !== null) {
            return [$decoded];
        }

        return is_array($value) ? $value : [$value];
    }

    public function hasRole($role)
    {
        return in_array($role, (array) $this->role);
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

    // public function getRoleAttribute($value)
    // {
    //     $decoded = json_decode($value, true);

    //     return is_array($decoded) ? $decoded : [$value];
    // }

    public function getRoleLabelAttribute(): string
    {
        return collect($this->role ?? [])
            ->map(fn($r) => ucwords(str_replace('_', ' ', $r)))
            ->join(', ');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
