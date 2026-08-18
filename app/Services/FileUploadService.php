<?php

namespace App\Services;

use App\Models\FileAsset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * SRD 36 / 52.1 - private file storage with MIME inspection and SHA-256 hashing.
 */
class FileUploadService
{
    private const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    private const DOCUMENT_TYPES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'text/csv' => 'csv',
        'text/plain' => 'txt',
        'application/vnd.ms-excel' => 'xls',
        'application/octet-stream' => 'bin',
    ];

    public function storeImage(UploadedFile $file, string $folder, User $user): FileAsset
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages(['photo' => 'The uploaded photograph could not be read.']);
        }

        $mime = $file->getMimeType();

        if (! isset(self::IMAGE_TYPES[$mime])) {
            throw ValidationException::withMessages(['photo' => 'Photographs must be JPEG, PNG or WebP images.']);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            throw ValidationException::withMessages(['photo' => 'Photographs must be smaller than 5 MB.']);
        }

        $content = file_get_contents($file->getRealPath());
        $sha = hash('sha256', $content);
        $extension = self::IMAGE_TYPES[$mime];
        $key = $folder.'/'.substr($sha, 0, 24).'.'.$extension;

        // Content-addressed storage: identical files share one asset row.
        $existing = FileAsset::where('object_key', $key)->first();

        if ($existing) {
            return $existing;
        }

        Storage::disk('private')->put($key, $content);

        $dimensions = @getimagesize($file->getRealPath());

        return FileAsset::create([
            'storage_disk' => 'private',
            'object_key' => $key,
            'original_name' => preg_replace('/[^\w\.\-]/', '_', $file->getClientOriginalName()),
            'mime_type' => $mime,
            'extension' => $extension,
            'size_bytes' => $file->getSize(),
            'sha256' => $sha,
            'malware_scan_status' => 'pending',
            'image_width' => $dimensions[0] ?? null,
            'image_height' => $dimensions[1] ?? null,
            'uploaded_by' => $user->id,
            'status' => 'available',
        ]);
    }

    public function storeDocument(UploadedFile $file, string $folder, User $user): FileAsset
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages(['documents' => 'One of the uploaded documents could not be read.']);
        }

        $mime = $file->getMimeType();

        if (! isset(self::DOCUMENT_TYPES[$mime])) {
            throw ValidationException::withMessages(['documents' => 'Documents must be PDF, JPEG, PNG, WebP or CSV/TXT files.']);
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            throw ValidationException::withMessages(['documents' => 'Each document must be smaller than 10 MB.']);
        }

        $content = file_get_contents($file->getRealPath());
        $sha = hash('sha256', $content);
        $extension = self::DOCUMENT_TYPES[$mime];
        $key = $folder.'/'.substr($sha, 0, 24).'.'.$extension;

        $existing = FileAsset::where('object_key', $key)->first();

        if ($existing) {
            return $existing;
        }

        Storage::disk('private')->put($key, $content);

        return FileAsset::create([
            'storage_disk' => 'private',
            'object_key' => $key,
            'original_name' => preg_replace('/[^\w\.\-]/', '_', $file->getClientOriginalName()),
            'mime_type' => $mime,
            'extension' => $extension,
            'size_bytes' => $file->getSize(),
            'sha256' => $sha,
            'malware_scan_status' => 'pending',
            'uploaded_by' => $user->id,
            'status' => 'available',
        ]);
    }
}
