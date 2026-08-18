@extends('layouts.app')

@section('title', 'Global Settings')
@section('page-title', 'Global Settings')
@section('page-subtitle', 'Configuration changes are versioned and audited. Secrets show only configured/not-configured state.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Settings</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            @include('partials.flash-messages')

            <form method="POST" action="{{ route('settings.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="border rounded-3 p-3 mb-4">
                    <h6 class="fw-semibold text-brand-navy mb-3">Branding &amp; SEO</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="settings_org_short_name" class="form-label small">Short name</label>
                            <input class="form-control form-control-sm" id="settings_org_short_name" name="settings[org_short_name]"
                                   value="{{ $settings->get('org_short_name')?->value ?? 'NIMCS' }}" maxlength="30">
                            <div class="form-text">Used in the browser title and brand, e.g. NIMCS.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Logo</label>
                            <input class="form-control form-control-sm" type="file" name="logo" accept="image/jpeg,image/png,image/webp">
                            @if ($brandLogoUrl)
                                <img src="{{ $brandLogoUrl }}" alt="Logo preview" class="mt-2 border rounded-3" style="height:52px;background:#fff;">
                            @endif
                            <div class="form-text">PNG/JPEG/WebP, max 2 MB. Used in the public header and portal sidebar.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Favicon</label>
                            <input class="form-control form-control-sm" type="file" name="favicon" accept="image/jpeg,image/png,image/webp">
                            @if ($brandFaviconUrl)
                                <img src="{{ $brandFaviconUrl }}" alt="Favicon preview" class="mt-2 border rounded-3" style="height:34px;background:#fff;">
                            @endif
                            <div class="form-text">PNG/JPEG/WebP, max 2 MB. Shown in the browser tab.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="settings_meta_description" class="form-label small">Meta description</label>
                            <textarea class="form-control form-control-sm" id="settings_meta_description" name="settings[meta_description]" rows="2" maxlength="400">{{ $settings->get('meta_description')?->value }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="settings_meta_keywords" class="form-label small">Meta keywords</label>
                            <input class="form-control form-control-sm" id="settings_meta_keywords" name="settings[meta_keywords]"
                                   value="{{ $settings->get('meta_keywords')?->value }}" maxlength="500">
                        </div>
                        <div class="col-md-6">
                            <label for="settings_meta_author" class="form-label small">Meta author</label>
                            <input class="form-control form-control-sm" id="settings_meta_author" name="settings[meta_author]"
                                   value="{{ $settings->get('meta_author')?->value }}" maxlength="200">
                        </div>
                        <div class="col-md-6">
                            <label for="settings_meta_og_title" class="form-label small">Open Graph title</label>
                            <input class="form-control form-control-sm" id="settings_meta_og_title" name="settings[meta_og_title]"
                                   value="{{ $settings->get('meta_og_title')?->value }}" maxlength="200">
                        </div>
                        <div class="col-12">
                            <label for="settings_meta_og_description" class="form-label small">Open Graph description</label>
                            <textarea class="form-control form-control-sm" id="settings_meta_og_description" name="settings[meta_og_description]" rows="2" maxlength="400">{{ $settings->get('meta_og_description')?->value }}</textarea>
                        </div>
                    </div>
                </div>

                @php
                    $booleanKeys = ['application_require_nin', 'certificate_expiry_enabled',
                        'notify_email_enabled', 'notify_sms_enabled', 'public_verification_show_photo', 'ninauth_enabled'];
                    $numberKeys = ['auth_session_idle_minutes', 'auth_session_max_hours', 'auth_trusted_device_days',
                        'application_due_days', 'application_plausible_age_min', 'application_plausible_age_max',
                        'documents_max_size_mb', 'documents_required_min', 'certificate_validity_years',
                        'certificate_number_padding', 'notify_digest_hour', 'retention_verification_events_days',
                        'retention_audit_days', 'retention_exports_days', 'verification_rate_limit_per_ip'];
                    $emailKeys = ['org_support_email'];
                @endphp

                @foreach ($groups as $group => $keys)
                    <div class="border rounded-3 p-3 mb-4">
                        <h6 class="fw-semibold text-brand-navy mb-3">{{ ucfirst($group) }}</h6>
                        <div class="row g-3">
                            @foreach ($keys as $key)
                                @php $value = $settings->get($key)?->value ?? ''; @endphp
                                <div class="col-md-6 col-lg-4">
                                    <label for="settings_{{ $key }}" class="form-label small">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                                    @if (in_array($key, $booleanKeys, true))
                                        <select class="form-select form-select-sm" id="settings_{{ $key }}" name="settings[{{ $key }}]">
                                            <option value="0" @selected($value === '0')>Off</option>
                                            <option value="1" @selected($value === '1')>On</option>
                                        </select>
                                    @else
                                        <input class="form-control form-control-sm" id="settings_{{ $key }}"
                                               name="settings[{{ $key }}]"
                                               type="{{ in_array($key, $numberKeys, true) ? 'number' : (in_array($key, $emailKeys, true) ? 'email' : 'text') }}"
                                               value="{{ $value }}">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="alert alert-info d-flex align-items-start gap-2">
                    <span class="material-symbols-outlined">key</span>
                    <div class="small">
                        Secrets (provider credentials, encryption/HMAC keys) are stored outside the
                        database in the environment/secret manager. They never appear on this page.
                    </div>
                </div>

                <button class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold" type="submit">
                    <i class="ri-save-line me-1"></i> Save settings
                </button>
            </form>
        </div>
    </div>
@endsection
