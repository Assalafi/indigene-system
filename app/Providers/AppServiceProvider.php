<?php

namespace App\Providers;

use App\Models\ApplicationDocument;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Indigene;
use App\Models\IndigeneApplication;
use App\Models\PrivacyRequest;
use App\Models\ReportExport;
use App\Models\User;
use App\Policies\ApplicationPolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\CertificatePolicy;
use App\Policies\CertificatePrintPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\IndigenePolicy;
use App\Policies\PrivacyRequestPolicy;
use App\Policies\ReportPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Cache-busting version for a brand asset so logo/favicon changes
     * are picked up without clearing the browser cache.
     */
    private function brandVersion(\App\Models\FileAsset $asset): string
    {
        if ($asset->updated_at) {
            return (string) $asset->updated_at->timestamp;
        }

        return substr((string) $asset->sha256, 0, 12);
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\RateLimiter::for('login', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by(strtolower($request->input('email', 'unknown')).'|'.$request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('certificate-verification', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(30)->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('certificate-token', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(30)->by($request->ip());
        });

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(IndigeneApplication::class, ApplicationPolicy::class);
        Gate::policy(Indigene::class, IndigenePolicy::class);
        Gate::policy(Certificate::class, CertificatePolicy::class);
        Gate::define('certificate.print-action', function (?User $user, Certificate $certificate) {
            if (! $user) {
                return false;
            }

            return app(CertificatePrintPolicy::class)->print($user, $certificate);
        });
        Gate::policy(ApplicationDocument::class, DocumentPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(PrivacyRequest::class, PrivacyRequestPolicy::class);
        Gate::policy(ReportExport::class, ReportPolicy::class);

        View::composer('partials.sidebar', function ($view) {
            if (auth()->check() && auth()->user()->can('application.decide')) {
                $lgaId = auth()->user()->activeLga()?->id;

                $count = \App\Models\IndigeneApplication::query()
                    ->when(! auth()->user()->isSystemAdmin(), fn ($q) => $q->where('lga_id', $lgaId))
                    ->whereIn('status', ['pending_chairman', 'pending_system_admin'])
                    ->where('created_by', '!=', auth()->id())
                    ->count();

                $view->with('pendingReviewCount', $count);
            }
        });

        View::composer(['partials.sidebar', 'partials.header'], function ($view) {
            if (auth()->check()) {
                $unread = auth()->user()->notifications()->whereNull('read_at')->count();
                $view->with('unreadNotifications', $unread);
            }
        });

        View::composer('partials.header', function ($view) {
            if (auth()->check()) {
                $view->with('headerNotifications', auth()->user()->notifications()->latest()->limit(6)->get());
            }
        });

        // Global brand + SEO variables shared across layouts and the public navbar.
        View::composer([
            'layouts.public', 'layouts.app', 'layouts.auth',
            'partials.styles', 'partials.navbar', 'partials.sidebar', 'partials.public-footer',
        ], function ($view) {
            $logoUrl = null;
            $logoId = \App\Models\SystemSetting::getSetting('org_logo_file_id');

            if ($logoId && ($logoAsset = \App\Models\FileAsset::find($logoId))) {
                $logoUrl = route('brand.logo').'?v='.$this->brandVersion($logoAsset);
            }

            $faviconUrl = route('brand.favicon');
            $faviconId = \App\Models\SystemSetting::getSetting('org_favicon_file_id');

            if ($faviconId && ($faviconAsset = \App\Models\FileAsset::find($faviconId))) {
                $faviconUrl = route('brand.favicon').'?v='.$this->brandVersion($faviconAsset);
            }

            $view->with('brandShortName', \App\Models\SystemSetting::getSetting('org_short_name', 'NIMCS'));
            $view->with('brandFaviconUrl', $faviconUrl);
            $view->with('brandLogoUrl', $logoUrl);
            $view->with('metaDescription', \App\Models\SystemSetting::getSetting('meta_description', 'Register and verify approved Nigerian indigene certificates securely.'));
            $view->with('metaKeywords', \App\Models\SystemSetting::getSetting('meta_keywords', 'indigene certificate, LGA approval, certificate verification, Nigeria'));
            $view->with('metaAuthor', \App\Models\SystemSetting::getSetting('meta_author', 'Haigha Tech'));
            $view->with('metaOgTitle', \App\Models\SystemSetting::getSetting('meta_og_title', 'Nigerian Indigene Management and Certification System'));
            $view->with('metaOgDescription', \App\Models\SystemSetting::getSetting('meta_og_description', 'Register indigenes through the authorised LGA workflow and verify issued certificates instantly.'));
        });
    }
}
