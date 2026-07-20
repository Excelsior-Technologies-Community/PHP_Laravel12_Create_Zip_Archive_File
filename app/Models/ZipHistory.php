<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'status' => 'success',
        'source' => 'upload',
    ];

    /**
     * Cast attributes.
     */
    protected $casts = [
        'total_files' => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

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
}
