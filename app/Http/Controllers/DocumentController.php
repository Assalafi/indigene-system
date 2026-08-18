<?php

namespace App\Http\Controllers;

use App\Models\ApplicationDocument;
use App\Models\FileAsset;
use App\Services\AuditService;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function download(ApplicationDocument $document)
    {
        $this->authorize('download', $document);

        $file = $document->fileAsset;

        if (! $file || ! Storage::disk($file->storage_disk)->exists($file->object_key)) {
            abort(404, 'The document is not available.');
        }

        if ($file->status === 'quarantined') {
            abort(423, 'This document is quarantined and cannot be downloaded.');
        }

        app(AuditService::class)->recordSensitiveAccess(
            \App\Models\Indigene::class,
            $document->profile->indigene_id,
            'application_document',
            'download',
            'Supporting document download'
        );

        return Storage::disk($file->storage_disk)->download(
            $file->object_key,
            $document->documentTypeLabel().'-'.$file->original_name
        );
    }

    public function staffPhoto(FileAsset $file)
    {
        $user = auth()->user();
        abort_unless($user && $user->isActive() && $user->can('application.view'), 403);

        if (! Storage::disk($file->storage_disk)->exists($file->object_key)) {
            abort(404);
        }

        return response(Storage::disk($file->storage_disk)->get($file->object_key))
            ->header('Content-Type', $file->mime_type)
            ->header('Cache-Control', 'no-store');
    }

    public function photo(FileAsset $file)
    {
        abort_unless(\App\Models\SystemSetting::getSetting('public_verification_show_photo', '0') === '1', 404);

        if (! Storage::disk($file->storage_disk)->exists($file->object_key)) {
            abort(404);
        }

        return response(Storage::disk($file->storage_disk)->get($file->object_key))
            ->header('Content-Type', $file->mime_type)
            ->header('Cache-Control', 'no-store');
    }
}
