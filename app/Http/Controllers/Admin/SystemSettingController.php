<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\AuditService;
use Illuminate\Http\Request;

/**
 * SRD 27.3 - global settings. Secrets show only configured/not-configured state.
 */
class SystemSettingController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index()
    {
        abort_unless(auth()->user()->can('settings.view') || auth()->user()->can('settings.manage'), 403);

        $groups = [
            'organisation' => ['org_name', 'org_provider_name', 'org_support_email', 'org_support_phone'],
            'authentication' => ['auth_session_idle_minutes', 'auth_session_max_hours', 'auth_trusted_device_days'],
            'applications' => ['application_due_days', 'application_require_nin', 'application_plausible_age_min', 'application_plausible_age_max'],
            'ninauth' => ['ninauth_enabled', 'ninauth_provider_name'],
            'documents' => ['documents_max_size_mb', 'documents_required_min'],
            'certificates' => ['certificate_expiry_enabled', 'certificate_validity_years', 'certificate_number_padding'],
            'notifications' => ['notify_email_enabled', 'notify_sms_enabled', 'notify_digest_hour'],
            'retention' => ['retention_verification_events_days', 'retention_audit_days', 'retention_exports_days'],
            'verification' => ['public_verification_show_photo', 'verification_rate_limit_per_ip'],
        ];

        $settings = SystemSetting::where('scope_type', 'global')->get()->keyBy('key');

        $brandLogoUrl = null;
        $logoId = $settings->get('org_logo_file_id')?->value;

        if ($logoId && ($logoAsset = \App\Models\FileAsset::find($logoId))) {
            $brandLogoUrl = route('brand.logo').'?v='.($logoAsset->updated_at?->timestamp ?? substr((string) $logoAsset->sha256, 0, 12));
        }

        $brandFaviconUrl = route('brand.favicon');
        $faviconId = $settings->get('org_favicon_file_id')?->value;

        if ($faviconId && ($faviconAsset = \App\Models\FileAsset::find($faviconId))) {
            $brandFaviconUrl = route('brand.favicon').'?v='.($faviconAsset->updated_at?->timestamp ?? substr((string) $faviconAsset->sha256, 0, 12));
        }

        return view('admin.settings.index', compact('groups', 'settings', 'brandLogoUrl', 'brandFaviconUrl'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('settings.manage'), 403);

        $knownKeys = collect([
            'org_name', 'org_provider_name', 'org_support_email', 'org_support_phone', 'org_short_name',
            'meta_description', 'meta_keywords', 'meta_author', 'meta_og_title', 'meta_og_description',
            'auth_session_idle_minutes', 'auth_session_max_hours', 'auth_trusted_device_days',
            'application_due_days', 'application_require_nin', 'application_plausible_age_min', 'application_plausible_age_max',
            'ninauth_enabled', 'ninauth_provider_name',
            'documents_max_size_mb', 'documents_required_min',
            'certificate_expiry_enabled', 'certificate_validity_years', 'certificate_number_padding',
            'notify_email_enabled', 'notify_sms_enabled', 'notify_digest_hour',
            'retention_verification_events_days', 'retention_audit_days', 'retention_exports_days',
            'public_verification_show_photo', 'verification_rate_limit_per_ip',
        ]);

        $data = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:2048'],
        ], [
            'logo.image' => 'The logo must be a JPEG, PNG or WebP image.',
            'favicon.image' => 'The favicon must be a JPEG, PNG or WebP image.',
        ]);

        $changed = [];

        foreach ($data['settings'] as $key => $value) {
            if (! $knownKeys->contains($key)) {
                continue;
            }

            $current = SystemSetting::where('key', $key)->where('scope_type', 'global')->first();

            if (! $current || $current->value !== $value) {
                SystemSetting::setSetting($key, $value);
                $changed[] = $key;
            }
        }

        if ($request->hasFile('logo')) {
            $file = app(\App\Services\FileUploadService::class)->storeImage($request->file('logo'), 'branding/logo', auth()->user());
            SystemSetting::setSetting('org_logo_file_id', $file->id);
            $changed[] = 'org_logo_file_id';
        }

        if ($request->hasFile('favicon')) {
            $file = app(\App\Services\FileUploadService::class)->storeImage($request->file('favicon'), 'branding/favicon', auth()->user());
            SystemSetting::setSetting('org_favicon_file_id', $file->id);
            $changed[] = 'org_favicon_file_id';
        }

        if ($changed) {
            $this->audit->record('settings.updated', SystemSetting::class, null, [], [
                'keys' => $changed,
            ], 'high');
        }

        return back()->with('status', count($changed).' setting(s) updated.');
    }
}

