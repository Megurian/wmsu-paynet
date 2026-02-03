<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_id',
        'user_id',
        'reason',
        'supporting_files',
        'status',
        'review_remark',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'supporting_files' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The reviewer (OSA user) who handled this appeal.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
