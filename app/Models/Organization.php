<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    // Allow assigning a mother organization when creating an office
    protected $fillable = ['name', 'org_code', 'role', 'college_id', 'logo', 'mother_organization_id'];

    // Eager-load common relations
    protected $with = ['admin', 'college'];

    public function admin()
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
}
