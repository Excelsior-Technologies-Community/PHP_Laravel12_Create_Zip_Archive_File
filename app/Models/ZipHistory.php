<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZipHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'zip_name',
        'total_files',
        'zip_size',
        'zip_path',
        'source',
        'status',
        'remarks',
        'is_encrypted',
        'encryption_method',
        'compression_level',
        'original_size_bytes',
        'compressed_size_bytes',
        'savings_percent',
        'checksum_sha256',
    ];

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'status' => 'success',
        'source' => 'upload',
        'is_encrypted' => false,
        'compression_level' => 'normal',
        'savings_percent' => 0.0,
    ];

    /**
     * Cast attributes.
     */
    protected $casts = [
        'total_files' => 'integer',
        'is_encrypted' => 'boolean',
        'original_size_bytes' => 'integer',
        'compressed_size_bytes' => 'integer',
        'savings_percent' => 'float',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Extracted histories relationship
     */
    public function extractions(): HasMany
    {
        return $this->hasMany(ZipExtractedHistory::class, 'zip_history_id')->latest();
    }

    /**
     * Scope for successful ZIP archives.
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed ZIP archives.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Get formatted creation date.
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at
            ? $this->created_at->format('d M Y h:i A')
            : '-';
    }

    /**
     * Get badge color according to source.
     */
    public function getSourceColorAttribute()
    {
        return match ($this->source) {
            'upload'         => 'success',
            'directory'      => 'primary',
            'selected_files' => 'warning',
            'command'        => 'info',
            default          => 'secondary',
        };
    }

    /**
     * Get badge color according to status.
     */
    public function getStatusColorAttribute()
    {
        return $this->status === 'success'
            ? 'success'
            : 'danger';
    }

    /**
     * Formatted human-readable original size
     */
    public function getFormattedOriginalSizeAttribute(): string
    {
        return $this->formatBytes($this->original_size_bytes);
    }

    /**
     * Check if archive is password encrypted
     */
    public function isEncrypted(): bool
    {
        return (bool) $this->is_encrypted;
    }

    /**
     * Format bytes into readable format (KB, MB, GB)
     */
    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
