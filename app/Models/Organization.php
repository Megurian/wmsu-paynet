<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    // Allow assigning a mother organization when creating an office
    protected $fillable = ['name', 'org_code', 'role', 'status', 'college_id', 'logo', 'mother_organization_id', 'inherits_osa_fees'];

    // Eager-load common relations
    protected $with = ['orgAdmin', 'college'];

    public function orgAdmin()
    {
        return $this->hasOne(User::class, 'organization_id')
                    ->whereIn('role', ['university_org', 'college_org']);
    }

    /**
     * The organization that acts as the "mother" for this organization (nullable).
     */
    public function motherOrganization()
    {
        return $this->belongsTo(Organization::class, 'mother_organization_id');
    }

    /**
     * Child organizations that reference this org as their mother.
     */
    public function childOrganizations()
    {
        return $this->hasMany(Organization::class, 'mother_organization_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the fees associated with this organization.
     */
    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    /**
     * Get the documents associated with this organization.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function createdSchoolYear() {
    return $this->belongsTo(SchoolYear::class, 'created_school_year_id');
}

public function createdSemester() {
    return $this->belongsTo(Semester::class, 'created_semester_id');
}
    
 
}
