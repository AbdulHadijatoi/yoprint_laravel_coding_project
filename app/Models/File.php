<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'checksum',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    protected $appends = [
        'file_url',
    ];

    public static function saveFile(UploadedFile $file, array $meta = []): self
    {
        $disk = config('filesystems.default', 'local');
        $checksum = hash_file('sha256', $file->getRealPath());
        $path = $file->store('uploads', $disk);

        return static::create([
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'checksum' => $checksum,
            'meta' => $meta,
        ]);
    }

    public function getFileUrlAttribute(): string
    {
        $disk = $this->disk ?? config('filesystems.default', 'local');
        return Storage::disk($disk)->url($this->path);
    }

    protected static function booted(): void
    {
        static::deleted(function (File $file) {
            $disk = $file->disk ?? config('filesystems.default', 'local');
            if ($file->path && Storage::disk($disk)->exists($file->path)) {
                Storage::disk($disk)->delete($file->path);
            }
        });
    }
}
