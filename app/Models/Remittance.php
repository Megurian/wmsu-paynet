<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remittance extends Model
{
    protected $fillable = [
        'from_organization_id',
        'to_organization_id',
        'fee_id',
        'amount',
        'school_year_id',
        'semester_id',
        'confirmed_by',
        'status'
    ];

    public function fromOrganization()
    {
        return $this->belongsTo(Organization::class,'from_organization_id');
    }

    public function toOrganization()
    {
        return $this->belongsTo(Organization::class,'to_organization_id');
    }

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class,'confirmed_by');
    }
}