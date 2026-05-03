<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $college_id
 * @property string $action
 * @property string|null $description
 * @property int|null $student_id
 * @property int|null $employee_id
 * @property int|null $officer_id
 * @property array<array-key, mixed>|null $meta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Student|null $student
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereCollegeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereOfficerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserId($value)
 */
	class ActivityLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string $message
 * @property string $starts_at
 * @property string|null $ends_at
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Announcement whereUpdatedAt($value)
 */
	class Announcement extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $fee_id
 * @property int $user_id
 * @property string $reason
 * @property array<array-key, mixed>|null $supporting_files
 * @property string|null $review_remark
 * @property int|null $reviewed_by
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Fee $fee
 * @property-read \App\Models\User|null $reviewer
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appeal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appeal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appeal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appeal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appeal whereFeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appeal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appeal whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appeal whereReviewRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appeal whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appeal whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appeal whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appeal whereSupportingFiles($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appeal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Appeal whereUserId($value)
 */
	class Appeal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $college_code
 * @property string|null $logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $admins
 * @property-read int|null $admins_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Course> $courses
 * @property-read int|null $courses_count
 * @property-read mixed $dean
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|College newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|College newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|College query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|College whereCollegeCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|College whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|College whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|College whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|College whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|College whereUpdatedAt($value)
 */
	class College extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $college_id
 * @property string $name
 * @property-read \App\Models\College $college
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Section> $sections
 * @property-read int|null $sections_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCollegeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereUpdatedAt($value)
 */
	class Course extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $organization_id
 * @property string|null $document_type
 * @property string $file_path
 * @property string $file_name
 * @property int $file_size
 * @property string $original_file_name
 * @property int $uploaded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organization $organization
 * @property-read \App\Models\User $uploadedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereOriginalFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUploadedBy($value)
 */
	class Document extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $college_id
 * @property string $first_name
 * @property string|null $middle_name
 * @property string|null $email
 * @property string $last_name
 * @property string|null $suffix
 * @property string|null $department
 * @property array<array-key, mixed>|null $position
 * @property int $has_account
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $user_id
 * @property int $is_active
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmployeeAssignment> $assignments
 * @property-read int|null $assignments_count
 * @property-read \App\Models\EmployeeAssignment|null $currentAssignment
 * @property-read mixed $formatted_roles
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCollegeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereHasAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSuffix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUserId($value)
 */
	class Employee extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $employee_id
 * @property int $school_year_id
 * @property int $semester_id
 * @property int|null $course_id
 * @property array<array-key, mixed>|null $positions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Course|null $course
 * @property-read \App\Models\Employee $employee
 * @property-read \App\Models\SchoolYear $schoolYear
 * @property-read \App\Models\Semester $semester
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeAssignment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeAssignment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeAssignment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeAssignment whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeAssignment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeAssignment whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeAssignment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeAssignment wherePositions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeAssignment whereSchoolYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeAssignment whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeAssignment whereUpdatedAt($value)
 */
	class EmployeeAssignment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $fee_scope
 * @property string|null $approval_level
 * @property int|null $organization_id
 * @property int|null $created_school_year_id
 * @property int|null $created_semester_id
 * @property int|null $college_id
 * @property int $user_id
 * @property string $fee_name
 * @property string $purpose
 * @property string $description
 * @property numeric $amount
 * @property string $recurrence
 * @property int|null $supporting_document_id
 * @property numeric|null $remittance_percent
 * @property string $requirement_level
 * @property int|null $accreditation_document_id
 * @property int|null $resolution_document_id
 * @property string|null $status
 * @property int|null $approved_by
 * @property string|null $approved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $disable_status
 * @property string|null $disable_reason
 * @property string|null $disable_requested_at
 * @property int|null $disable_requested_by
 * @property int|null $disable_approved_by
 * @property string|null $disable_approved_at
 * @property string|null $disable_notes
 * @property-read \App\Models\Document|null $accreditationDocument
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Appeal> $appeals
 * @property-read int|null $appeals_count
 * @property-read \App\Models\College|null $college
 * @property-read \App\Models\SchoolYear|null $createdSchoolYear
 * @property-read \App\Models\Semester|null $createdSemester
 * @property-read \App\Models\User $creator
 * @property-read \App\Models\User|null $disableApprovedBy
 * @property-read \App\Models\User|null $disableRequestedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FeeRequest> $feeRequests
 * @property-read int|null $fee_requests_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\Document|null $resolutionDocument
 * @property-read \App\Models\Document|null $supportingDocument
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereAccreditationDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereApprovalLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereCollegeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereCreatedSchoolYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereCreatedSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereDisableApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereDisableApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereDisableNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereDisableReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereDisableRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereDisableRequestedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereDisableStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereFeeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereFeeScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereRecurrence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereRemittancePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereRequirementLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereResolutionDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereSupportingDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Fee whereUserId($value)
 */
	class Fee extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $fee_id
 * @property string $type
 * @property string $status
 * @property string $reason
 * @property int|null $requested_by
 * @property string|null $requested_at
 * @property int|null $reviewed_by
 * @property string|null $reviewed_at
 * @property string|null $review_note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $disable_approved_at
 * @property string|null $enable_approved_at
 * @property string|null $enabled_at
 * @property string|null $disabled_at
 * @property-read \App\Models\Fee $fee
 * @property-read \App\Models\User|null $requestedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereDisableApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereDisabledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereEnableApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereEnabledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereFeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereRequestedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereReviewNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeRequest whereUpdatedAt($value)
 */
	class FeeRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $org_code
 * @property string $role
 * @property string|null $status
 * @property int $inherits_osa_fees If true, this organization will inherit fees created by the Office of Student Affairs (OSA)
 * @property string|null $approved_at
 * @property int|null $college_id
 * @property string|null $logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $mother_organization_id
 * @property int|null $created_school_year_id
 * @property int|null $created_semester_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Organization> $childOrganizations
 * @property-read int|null $child_organizations_count
 * @property-read \App\Models\College|null $college
 * @property-read \App\Models\SchoolYear|null $createdSchoolYear
 * @property-read \App\Models\Semester|null $createdSemester
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fee> $fees
 * @property-read int|null $fees_count
 * @property-read Organization|null $motherOrganization
 * @property-read \App\Models\User|null $orgAdmin
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereCollegeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereCreatedSchoolYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereCreatedSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereInheritsOsaFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereMotherOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereOrgCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organization whereUpdatedAt($value)
 */
	class Organization extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int|null $user_id
 * @property int $organization_id
 * @property int $semester_id
 * @property string $role
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organization $organization
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationOfficer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationOfficer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationOfficer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationOfficer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationOfficer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationOfficer whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationOfficer whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationOfficer whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationOfficer whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationOfficer whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationOfficer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganizationOfficer whereUserId($value)
 */
	class OrganizationOfficer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $transaction_id
 * @property string $payment_type
 * @property int $student_id
 * @property int $enrollment_id
 * @property int|null $organization_id
 * @property numeric $amount_due
 * @property numeric $cash_received
 * @property numeric $change
 * @property int $collected_by
 * @property int|null $school_year_id
 * @property int|null $semester_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $promissory_note_id
 * @property string|null $notes
 * @property-read \App\Models\User $collector
 * @property-read \App\Models\StudentEnrollment $enrollment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fee> $fees
 * @property-read int|null $fees_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\PromissoryNote|null $promissoryNote
 * @property-read \App\Models\SchoolYear|null $schoolYear
 * @property-read \App\Models\Semester|null $semester
 * @property-read \App\Models\Student $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment cash()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment promissory()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmountDue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCashReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCollectedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereEnrollmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePromissoryNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereSchoolYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 */
	class Payment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int|null $open_student_id
 * @property int $enrollment_id
 * @property int $issued_by
 * @property string $status
 * @property numeric $original_amount
 * @property numeric $remaining_balance
 * @property \Illuminate\Support\Carbon $due_date
 * @property \Illuminate\Support\Carbon $signature_deadline
 * @property \Illuminate\Support\Carbon|null $signed_at
 * @property int|null $signed_by
 * @property string|null $document_path
 * @property \Illuminate\Support\Carbon|null $adviser_reviewed_at
 * @property int|null $adviser_reviewed_by
 * @property string|null $adviser_review_notes
 * @property \Illuminate\Support\Carbon|null $default_date
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\StudentEnrollment $enrollment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fee> $fees
 * @property-read int|null $fees_count
 * @property-read \App\Models\User $issuedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\Student|null $signatary
 * @property-read \App\Models\Student $student
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote badDebt()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote closed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote defaulted()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote forEnrollment($enrollment_id)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote forStudent($student_id)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote overdue()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote pendingAdviserVerification()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote pendingVerification()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote voided()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereAdviserReviewNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereAdviserReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereAdviserReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereDefaultDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereDocumentPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereEnrollmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereIssuedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereOpenStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereOriginalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereRemainingBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereSignatureDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereSignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereSignedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromissoryNote whereUpdatedAt($value)
 */
	class PromissoryNote extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $from_organization_id
 * @property int $to_organization_id
 * @property int|null $fee_id
 * @property numeric $amount
 * @property string|null $proof_image
 * @property int|null $school_year_id
 * @property int|null $semester_id
 * @property int|null $confirmed_by
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $confirmer
 * @property-read \App\Models\Fee|null $fee
 * @property-read \App\Models\Organization $fromOrganization
 * @property-read \App\Models\Organization $toOrganization
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance whereConfirmedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance whereFeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance whereFromOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance whereProofImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance whereSchoolYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance whereToOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Remittance whereUpdatedAt($value)
 */
	class Remittance extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon $sy_start
 * @property \Illuminate\Support\Carbon $sy_end
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Semester|null $activeSemester
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Semester> $semesters
 * @property-read int|null $semesters_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolYear newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolYear newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolYear query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolYear whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolYear whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolYear whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolYear whereSyEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolYear whereSyStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SchoolYear whereUpdatedAt($value)
 */
	class SchoolYear extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $college_id
 * @property string $name
 * @property-read \App\Models\College $college
 * @property-read \App\Models\Course|null $course
 * @property-read \App\Models\YearLevel|null $yearLevel
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereCollegeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereUpdatedAt($value)
 */
	class Section extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $school_year_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $starts_at
 * @property \Illuminate\Support\Carbon|null $will_end_at
 * @property bool $is_active
 * @property bool $is_auto
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SchoolYear $schoolYear
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereEndedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereIsAuto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereSchoolYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Semester whereWillEndAt($value)
 */
	class Semester extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $student_id
 * @property string $last_name
 * @property string $first_name
 * @property string|null $middle_name
 * @property string|null $suffix
 * @property string|null $contact
 * @property string|null $email
 * @property bool $is_graduated
 * @property bool $is_officer
 * @property string|null $password
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property string|null $religion
 * @property-read \App\Models\StudentEnrollment|null $currentEnrollment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StudentEnrollment> $enrollments
 * @property-read int|null $enrollments_count
 * @property-read mixed $full_name
 * @property-read \App\Models\StudentEnrollment|null $latestEnrollment
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrganizationOfficer> $organizationOfficers
 * @property-read int|null $organization_officers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PromissoryNote> $promissoryNotes
 * @property-read int|null $promissory_notes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student blockedFromNextSemester()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereIsGraduated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereIsOfficer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereReligion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereSuffix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Student whereUpdatedAt($value)
 */
	class Student extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $student_id
 * @property int $college_id
 * @property int $course_id
 * @property int $year_level_id
 * @property int $section_id
 * @property int $school_year_id
 * @property int $semester_id
 * @property string $status
 * @property bool $is_void
 * @property string $financial_status
 * @property int|null $adviser_id
 * @property \Illuminate\Support\Carbon|null $advised_at
 * @property int|null $validated_by
 * @property \Illuminate\Support\Carbon|null $validated_at
 * @property int|null $assessed_by
 * @property \Illuminate\Support\Carbon|null $assessed_at
 * @property bool|null $cleared_for_enrollment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PromissoryNote|null $activePromissoryNote
 * @property-read \App\Models\User|null $adviser
 * @property-read \App\Models\User|null $assessor
 * @property-read \App\Models\Course $course
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PromissoryNote> $promissoryNotes
 * @property-read int|null $promissory_notes_count
 * @property-read \App\Models\SchoolYear $schoolYear
 * @property-read \App\Models\Section $section
 * @property-read \App\Models\Semester $semester
 * @property-read \App\Models\Student $student
 * @property-read \App\Models\YearLevel $yearLevel
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment financiallyCleared()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment financiallyDeferred()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereAdvisedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereAdviserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereAssessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereAssessedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereClearedForEnrollment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereCollegeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereFinancialStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereIsVoid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereSchoolYearId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereSemesterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereStudentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereValidatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereValidatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StudentEnrollment whereYearLevelId($value)
 */
	class StudentEnrollment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SystemSetting whereValue($value)
 */
	class SystemSetting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $last_name
 * @property string $first_name
 * @property string|null $middle_name
 * @property string|null $suffix
 * @property string $email
 * @property array<array-key, mixed> $role
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $invitation_sent_at
 * @property \Illuminate\Support\Carbon|null $invitation_accepted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $college_id
 * @property int|null $course_id
 * @property int|null $organization_id
 * @property-read \App\Models\College|null $college
 * @property-read \App\Models\Course|null $course
 * @property-read \App\Models\Employee|null $employee
 * @property-read string $full_name
 * @property-read bool $invitation_active
 * @property-read bool $invitation_expired
 * @property-read mixed $invitation_expires_at
 * @property-read bool $invitation_pending
 * @property-read string|null $invitation_status
 * @property-read string $role_label
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Organization|null $organization
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCollegeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereInvitationAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereInvitationSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSuffix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $college_id
 * @property string $name
 * @property-read \App\Models\College $college
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Section> $sections
 * @property-read int|null $sections_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearLevel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearLevel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearLevel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearLevel whereCollegeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearLevel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearLevel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearLevel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|YearLevel whereUpdatedAt($value)
 */
	class YearLevel extends \Eloquent {}
}

