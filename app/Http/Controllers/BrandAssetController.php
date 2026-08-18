<?php

namespace App\Http\Controllers;

use App\Models\FileAsset;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves uploaded brand assets (logo, favicon) configured in global settings.
 * Falls back to the packaged favicon so browsers never request a missing icon.
 */
class BrandAssetController extends Controller
{
    public function favicon(): Response
    {
        $asset = $this->settingAsset('org_favicon_file_id');

        if ($asset) {
            return $this->serve($asset);
        }

        return response()->file(
            public_path('assets/images/favicon.png'),
            ['Cache-Control' => 'public, max-age=86400']
        );
    }

    public function logo(): Response
    {
        $asset = $this->settingAsset('org_logo_file_id');

        if (! $asset) {
            abort(404, 'No custom logo is configured.');
        }

        return $this->serve($asset);
    }

    private function settingAsset(string $key): ?FileAsset
    {
        $id = SystemSetting::getSetting($key);

        if (! $id) {
            return null;
        }

        $asset = FileAsset::find($id);

        if (! $asset || ! Storage::disk($asset->storage_disk)->exists($asset->object_key)) {
            return null;
        }

        return $asset;
    }

    private function serve(FileAsset $asset): Response
    {
        return response(Storage::disk($asset->storage_disk)->get($asset->object_key))
            ->header('Content-Type', $asset->mime_type)
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
