<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUpload extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_FAILED = 'failed';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'file_id',
        'status',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'started_at',
        'finished_at',
        'error_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected $appends = [
        'file_url',
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file ? $this->file->file_url : null;
    }

    protected static function booted(): void
    {
        static::deleting(function (self $upload) {
            if ($upload->file) {
                $upload->file->delete();
            }
        });
    }
}
