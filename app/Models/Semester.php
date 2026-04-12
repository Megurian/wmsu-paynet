<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = ['school_year_id', 'name', 'starts_at', 'will_end_at', 'is_active', 'started_at', 'ended_at'];

    protected $casts = [
        'starts_at' => 'date',
        'will_end_at' => 'date',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function schoolYear() {
        return $this->belongsTo(SchoolYear::class);
    }

    public function plannedStartDate(): ?Carbon
    {
        return $this->starts_at?->copy();
    }

    public function actualStartDate(): ?Carbon
    {
        return $this->started_at?->copy();
    }

    public function plannedEndDate(): ?Carbon
    {
        if ($this->will_end_at) {
            return $this->will_end_at->copy();
        }

        return $this->schoolYear?->sy_end?->copy();
    }

    public function effectiveEndDate(): ?Carbon
    {
        if ($this->ended_at) {
            return $this->ended_at->copy();
        }

        return $this->plannedEndDate();
    }
}
