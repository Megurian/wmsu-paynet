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
        'accreditation_document_id',
        'resolution_document_id',
        'supporting_document_id',
        'status',
        'fee_scope',
        'college_id',
        'approval_level',
        'approved_by',
        'approved_at',
         'created_school_year_id',
    'created_semester_id',
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
        return $this->belongsToMany(Payment::class, 'fee_payment')->withPivot('amount_paid')->withTimestamps();
    }

    /**
     * Get the accreditation document associated with this fee.
     */
    public function accreditationDocument()
    {
        return $this->belongsTo(Document::class, 'accreditation_document_id');
    }

    /**
     * Get the resolution document associated with this fee.
     */
    public function resolutionDocument()
    {
        return $this->belongsTo(Document::class, 'resolution_document_id');
    }

    /**
     * Get the supporting document associated with this fee (1:1 relationship).
     */
    public function supportingDocument()
    {
        return $this->belongsTo(Document::class, 'supporting_document_id');
    }
    public function createdSchoolYear() {
    return $this->belongsTo(SchoolYear::class, 'created_school_year_id');
}

public function createdSemester() {
    return $this->belongsTo(Semester::class, 'created_semester_id');
}
}
