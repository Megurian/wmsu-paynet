<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'message',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->whereDate('starts_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhereDate('ends_at', '>=', now());
            });
    }
}
