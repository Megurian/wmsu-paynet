<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'organization_id',
        'document_type',
        'file_path',
        'file_name',
        'file_size',
        'original_file_name',
        'uploaded_by',
    ];

    /**
     * Get the organization that owns the document.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
