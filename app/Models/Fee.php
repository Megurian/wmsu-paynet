<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'disable_status',
        'disable_reason',
        'disable_requested_at',
        'disable_requested_by',
        'disable_approved_at',
        'disable_approved_by',
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

    public static function paidFeeIdsForStudentByPeriod(int $studentId, array $feeIds, ?int $schoolYearId, ?int $semesterId): array
    {
        if (empty($feeIds)) {
            return [];
        }

        return DB::table('fee_payment')
            ->join('payments', 'fee_payment.payment_id', '=', 'payments.id')
            ->join('fees', 'fee_payment.fee_id', '=', 'fees.id')
            ->where('payments.student_id', $studentId)
            ->whereIn('fee_payment.fee_id', $feeIds)
            ->where(function ($query) use ($schoolYearId, $semesterId) {
                $query->where('fees.recurrence', 'one_time')
                    ->orWhere(function ($q) use ($schoolYearId) {
                        if ($schoolYearId) {
                            $q->where('fees.recurrence', 'annual')
                                ->where('payments.school_year_id', $schoolYearId);
                        } else {
                            $q->whereRaw('0 = 1');
                        }
                    })
                    ->orWhere(function ($q) use ($semesterId) {
                        if ($semesterId) {
                            $q->where('fees.recurrence', 'semestrial')
                                ->where('payments.semester_id', $semesterId);
                        } else {
                            $q->whereRaw('0 = 1');
                        }
                    });
            })
            ->groupBy('fee_payment.fee_id')
            ->pluck('fee_payment.fee_id')
            ->unique()
            ->toArray();
    }

    public function createdSchoolYear() {
    return $this->belongsTo(SchoolYear::class, 'created_school_year_id');
}

public function createdSemester() {
    return $this->belongsTo(Semester::class, 'created_semester_id');
}

public function disableRequestedBy()
{
    return $this->belongsTo(User::class, 'disable_requested_by');
}


public function disableApprovedBy()
{
    return $this->belongsTo(User::class, 'disable_approved_by');
}

// App\Models\Fee.php

public function feeRequests()
{
    return $this->hasMany(FeeRequest::class);
}
}
