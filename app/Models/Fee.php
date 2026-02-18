<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'user_id',
        'fee_name',
        'purpose',
        'description',
        'amount',
        'remittance_percent',
        'requirement_level',
        'recurrence',
        'accreditation_file',
        'resolution_file',
        'status',
        'fee_scope',
        'college_id',
        'approval_level',
        'approved_by',
        'approved_at',
    ];

    /**
     * Get the organization that owns the fee.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user who created the fee.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Appeals submitted for this fee.
     */
    public function appeals()
    {
        return $this->hasMany(Appeal::class);
    }

     public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payments() {
        return $this->belongsToMany(Payment::class, 'fee_payment')->withPivot('amount')->withTimestamps();
    }
}
