<?php

use App\Http\Controllers\Admin\FraudReportController as AdminFraudReportController;
use App\Http\Controllers\Admin\LgaProfileController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationDecisionController;
use App\Http\Controllers\ApplicationWizardController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BrandAssetController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DuplicateReviewController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GeographyController;
use App\Http\Controllers\GeographyImportController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\IndigeneController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PrivacyRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\FraudReportController as PublicFraudReportController;
use App\Http\Controllers\Public\LandingPageController;
use App\Http\Controllers\Public\PublicCertificateVerificationController;
use App\Http\Controllers\Public\StaticPageController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// --------------------------------------------------------------------------
// Public routes (SRD 30.1)
// --------------------------------------------------------------------------
Route::get('/', LandingPageController::class)->name('home');

Route::get('/verify', [PublicCertificateVerificationController::class, 'create'])
    ->name('certificates.verify.form');
Route::post('/verify', [PublicCertificateVerificationController::class, 'store'])
    ->middleware('throttle:certificate-verification')
    ->name('certificates.verify');
Route::get('/v/{token}', [PublicCertificateVerificationController::class, 'show'])
    ->middleware('throttle:certificate-token')
    ->name('certificates.verify.token');

Route::get('/fraud-reports', [PublicFraudReportController::class, 'create'])->name('fraud-reports.create');
Route::post('/fraud-reports', [PublicFraudReportController::class, 'store'])->name('fraud-reports.store');

Route::get('/privacy', [StaticPageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [StaticPageController::class, 'terms'])->name('terms');
Route::get('/accessibility', [StaticPageController::class, 'accessibility'])->name('accessibility');
Route::get('/support', [StaticPageController::class, 'support'])->name('support');
Route::get('/system-status', [StaticPageController::class, 'systemStatus'])->name('system-status');

Route::get('/public/photos/{file}', [DocumentController::class, 'photo'])->name('public.photo');

// Brand assets configured in global settings (public, cached).
Route::get('/brand/favicon', [BrandAssetController::class, 'favicon'])->name('brand.favicon');
Route::get('/brand/logo', [BrandAssetController::class, 'logo'])->name('brand.logo');
// --------------------------------------------------------------------------
// Authentication (SRD 18)
// --------------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');

    Route::get('/activate/{token}', [ActivationController::class, 'show'])->name('activation.show');
    Route::post('/activate/{token}', [ActivationController::class, 'store'])->name('activation.store');

    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/password/new', [PasswordResetController::class, 'showNewPassword'])->name('password.new');
    Route::post('/password/new', [PasswordResetController::class, 'setNewPassword'])->name('password.new.store');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --------------------------------------------------------------------------
// Authenticated staff portal (SRD 30.2)
// --------------------------------------------------------------------------
Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('/password/change', [PasswordChangeController::class, 'showForced'])->name('password.change');
    Route::post('/password/change', [PasswordChangeController::class, 'update'])->name('password.change.store');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Applications
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/create', [ApplicationWizardController::class, 'create'])->name('applications.create');
    Route::get('/applications/{application}/edit', [ApplicationWizardController::class, 'edit'])->name('applications.edit');
    Route::post('/applications/{application}/save', [ApplicationWizardController::class, 'saveAndSubmit'])->name('applications.save');
    Route::get('/applications/{application}/wizard/{step}', [ApplicationWizardController::class, 'show'])
        ->where('step', '[1-8]')
        ->name('applications.wizard');
    Route::post('/applications/{application}/wizard/{step}', [ApplicationWizardController::class, 'store'])
        ->where('step', '[1-8]')
        ->name('applications.wizard.store');
    Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');

    // Approvals
    Route::get('/approvals', [ApplicationDecisionController::class, 'queue'])->name('approvals.queue');
    Route::post('/applications/{application}/decisions', [ApplicationDecisionController::class, 'decide'])
        ->name('applications.decide');

    // Duplicate review
    Route::get('/duplicates', [DuplicateReviewController::class, 'index'])->name('duplicates.index');
    Route::post('/duplicates/{flag}/resolve', [DuplicateReviewController::class, 'resolve'])->name('duplicates.resolve');

    // Indigenes
    Route::get('/indigenes', [IndigeneController::class, 'index'])->name('indigenes.index');
    Route::get('/indigenes/search', [IndigeneController::class, 'search'])->name('indigenes.search');
    Route::get('/indigenes/{indigene}', [IndigeneController::class, 'show'])->name('indigenes.show');
    Route::post('/indigenes/{indigene}/amendments', [IndigeneController::class, 'startAmendment'])->name('indigenes.amendments');
    Route::post('/indigenes/{indigene}/reveal-nin', [IndigeneController::class, 'revealNin'])->name('indigenes.reveal-nin');

    // Documents
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/staff/photos/{file}', [DocumentController::class, 'staffPhoto'])->name('documents.photo');

    // Certificates
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/print-history', [CertificateController::class, 'printHistory'])->name('certificates.print-history');
    Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');
    Route::post('/certificates/{certificate}/issue', [CertificateController::class, 'issue'])->name('certificates.issue');
    Route::post('/certificates/{certificate}/print-events', [CertificateController::class, 'createPrint'])->name('certificates.print-events');
    Route::get('/certificates/{certificate}/print-events/{event}', [CertificateController::class, 'printResult'])->name('certificates.print-result');
    Route::get('/certificates/{certificate}/print-events/{event}/download', [CertificateController::class, 'download'])->name('certificates.download');
    Route::post('/certificates/{certificate}/suspend', [CertificateController::class, 'suspend'])->name('certificates.suspend');
    Route::post('/certificates/{certificate}/reinstate', [CertificateController::class, 'reinstate'])->name('certificates.reinstate');
    Route::post('/certificates/{certificate}/revoke', [CertificateController::class, 'revoke'])->name('certificates.revoke');
    Route::post('/certificates/{certificate}/reissue', [CertificateController::class, 'reissue'])->name('certificates.reissue');

    // Geography
    Route::get('/geography', [GeographyController::class, 'states'])->name('geography.states');
    Route::get('/geography/lgas/{lga}', [GeographyController::class, 'showLga'])->name('geography.lga-show');
    Route::get('/geography/wards', [GeographyController::class, 'wards'])->name('geography.wards');
    Route::post('/geography/wards', [GeographyController::class, 'storeWard'])->name('geography.wards.store');
    Route::post('/geography/units', [GeographyController::class, 'storeLocalUnit'])->name('geography.units.store');
    Route::post('/geography/districts', [GeographyController::class, 'storeDistrict'])->name('geography.districts.store');
    Route::post('/geography/update', [GeographyController::class, 'update'])->name('geography.update');
    Route::post('/geography/destroy', [GeographyController::class, 'destroy'])->name('geography.destroy');
    Route::post('/geography/retire', [GeographyController::class, 'retire'])->name('geography.retire');
    Route::get('/api/geography/lgas-by-state', [GeographyController::class, 'lgasByState'])->name('api.geography.lgas-by-state');
    Route::get('/api/geography/wards-by-lga', [GeographyController::class, 'wardsByLga'])->name('api.geography.wards-by-lga');
    Route::get('/geography/imports', [GeographyImportController::class, 'index'])->name('geography.imports.index');
    Route::get('/geography/imports/create', [GeographyImportController::class, 'create'])->name('geography.imports.create');
    Route::post('/geography/imports', [GeographyImportController::class, 'store'])->name('geography.imports.store');
    Route::get('/geography/imports/{batch}', [GeographyImportController::class, 'show'])->name('geography.imports.show');
    Route::post('/geography/imports/{batch}/publish', [GeographyImportController::class, 'publish'])->name('geography.imports.publish');

    // Reports and exports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{code}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/exports', [ExportController::class, 'myExports'])->name('reports.exports');
    Route::post('/exports', [ExportController::class, 'create'])->name('exports.create');
    Route::get('/exports/{export}/download', [ExportController::class, 'download'])->name('exports.download');

    // Audit & security
    Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
    Route::get('/audit/sensitive-access', [AuditLogController::class, 'sensitiveAccess'])->name('audit.sensitive-access');
    Route::get('/audit/login-events', [AuditLogController::class, 'loginEvents'])->name('audit.login-events');
    Route::get('/audit/{log}', [AuditLogController::class, 'show'])->name('audit.show');

    // Privacy
    Route::get('/privacy-requests', [PrivacyRequestController::class, 'index'])->name('privacy.requests.index');
    Route::get('/privacy-requests/create', [PrivacyRequestController::class, 'create'])->name('privacy.requests.create');
    Route::post('/privacy-requests', [PrivacyRequestController::class, 'store'])->name('privacy.requests.store');
    Route::get('/privacy-requests/{privacyRequest}', [PrivacyRequestController::class, 'show'])->name('privacy.requests.show');
    Route::post('/privacy-requests/{privacyRequest}/decide', [PrivacyRequestController::class, 'decide'])->name('privacy.requests.decide');
    Route::get('/legal-holds', [PrivacyRequestController::class, 'holds'])->name('privacy.holds.index');
    Route::post('/legal-holds', [PrivacyRequestController::class, 'storeHold'])->name('privacy.holds.store');
    Route::post('/legal-holds/{hold}/release', [PrivacyRequestController::class, 'releaseHold'])->name('privacy.holds.release');

    // Users (System Admin only)
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
    Route::post('/admin/users/{user}/status', [UserController::class, 'toggleStatus'])->name('admin.users.status');
    Route::post('/admin/users/{user}/resend-activation', [UserController::class, 'resendActivation'])->name('admin.users.resend-activation');
    Route::post('/admin/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::post('/admin/users/{user}/assignments', [UserController::class, 'storeAssignment'])->name('admin.users.assignments.store');
    Route::post('/admin/assignments/{assignment}/end', [UserController::class, 'endAssignment'])->name('admin.users.assignments.end');
    Route::post('/admin/users/{user}/role', [UserController::class, 'updateRole'])->name('admin.users.role');

    // LGA profiles & signatories
    Route::get('/admin/lga-profiles', [LgaProfileController::class, 'index'])->name('admin.lga-profiles.index');
    Route::get('/admin/lga-profiles/{lga}', [LgaProfileController::class, 'show'])->name('admin.lga-profiles.show');
    Route::post('/admin/lga-profiles/{lga}', [LgaProfileController::class, 'store'])->name('admin.lga-profiles.store');
    Route::post('/admin/lga-profiles/{lga}/signatories', [LgaProfileController::class, 'storeSignatory'])->name('admin.lga-profiles.signatories.store');

    // Fraud report management
    Route::get('/admin/fraud-reports', [AdminFraudReportController::class, 'index'])->name('admin.fraud-reports.index');
    Route::get('/admin/fraud-reports/{report}', [AdminFraudReportController::class, 'show'])->name('admin.fraud-reports.show');
    Route::post('/admin/fraud-reports/{report}/resolve', [AdminFraudReportController::class, 'resolve'])->name('admin.fraud-reports.resolve');

    // Global settings
    Route::get('/admin/settings', [SystemSettingController::class, 'index'])->name('settings.index');
    Route::post('/admin/settings', [SystemSettingController::class, 'store'])->name('settings.store');

    // Profile, notifications, help
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
});
