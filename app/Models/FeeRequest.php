<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeRequest extends Model
{
    protected $fillable = [
        'fee_id',
        'type',
        'status',
        'reason',
        'requested_by',
        'requested_at',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    public function fee()
{
    return $this->belongsTo(Fee::class);
}

public function requestedBy()
{
    return $this->belongsTo(User::class, 'requested_by');
}

    
}