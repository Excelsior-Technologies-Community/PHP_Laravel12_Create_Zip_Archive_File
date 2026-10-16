<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZipExtractedHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'zip_history_id',
        'archive_name',
        'extracted_folder',
        'total_files_extracted',
        'total_size_bytes',
        'status',
        'extracted_at',
    ];

    protected $casts = [
        'total_files_extracted' => 'integer',
        'total_size_bytes' => 'integer',
        'extracted_at' => 'datetime',
    ];

    public function zipHistory(): BelongsTo
    {
        return $this->belongsTo(ZipHistory::class, 'zip_history_id');
    }

    public function getFormattedSizeAttribute(): string
    {
        return ZipHistory::formatBytes($this->total_size_bytes);
    }
}
