<?php

namespace App\Services;

use App\Enums\CertificateStatus;
use App\Models\Certificate;
use App\Models\CertificatePrintEvent;
use App\Models\CertificateStatusEvent;
use App\Models\CertificateVersion;
use App\Models\IndigeneProfile;
use App\Models\LgaProfile;
use App\Models\OfficialSignatory;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * SRD 15 / 48 - immutable certificate snapshots and deterministic PDF rendering.
 */
class CertificateRenderService
{
    public function __construct(
        private CertificateNumberService $numbers,
        private AuditService $audit,
    ) {}

    public function issue(Certificate $certificate, User $user): CertificateVersion
    {
        if ($certificate->status !== CertificateStatus::Eligible) {
            throw new HttpException(409, 'This certificate is not eligible for issuance.');
        }

        $indigene = $certificate->indigene;
        $profile = $indigene->currentProfile;

        if (! $profile) {
            throw new HttpException(409, 'No approved profile exists for this indigene.');
        }

        $lga = $certificate->lga;
        $lgaProfile = LgaProfile::where('lga_id', $lga->id)
            ->where('status', 'published')
            ->orderByDesc('version_no')
            ->first();

        $signatory = OfficialSignatory::where('lga_id', $lga->id)
            ->where('status', 'active')
            ->where('is_primary', true)
            ->first();

        if (! $lgaProfile || ! $signatory) {
            throw new HttpException(409, 'LGA branding and an active signatory are required before a certificate can be issued.');
        }

        $number = $certificate->certificate_number ?? $this->numbers->allocate($lga);

        $publicToken = Str::random(32);
        $verificationUrl = route('certificates.verify.token', ['token' => $publicToken]);

        $snapshot = $this->buildSnapshot($certificate, $profile, $lgaProfile, $signatory, $number, $verificationUrl);

        return DB::transaction(function () use ($certificate, $profile, $lgaProfile, $signatory, $number, $publicToken, $verificationUrl, $snapshot, $user) {
            $versionNo = CertificateVersion::where('certificate_id', $certificate->id)->max('version_no') + 1;

            $version = CertificateVersion::create([
                'certificate_id' => $certificate->id,
                'version_no' => $versionNo,
                'certificate_template_id' => $snapshot['template_id'],
                'lga_profile_id' => $lgaProfile->id,
                'signatory_id' => $signatory->id,
                'source_profile_id' => $profile->id,
                'snapshot_ciphertext' => Crypt::encryptString(json_encode($snapshot)),
                'qr_payload_hash' => hash('sha256', $verificationUrl),
                'generated_by' => $user->id,
                'generated_at' => now(),
                'status' => 'active',
            ]);

            $certificate->certificate_number = $number;
            $certificate->status = CertificateStatus::Active;
            $certificate->current_version_id = $version->id;
            $certificate->public_token_hash = hash('sha256', $publicToken);
            $certificate->public_token_hint = substr($publicToken, 0, 8);
            $certificate->issued_at = $certificate->issued_at ?? now();
            $certificate->save();

            $pdf = $this->renderPdf($snapshot, $verificationUrl);
            $version->pdf_sha256 = hash('sha256', $pdf);
            $version->save();

            return $version;
        });
    }

    public function reissue(Certificate $certificate, User $user, string $reasonCode, string $reasonText): CertificateVersion
    {
        if ($certificate->status !== CertificateStatus::Active) {
            throw new HttpException(409, 'Only an active certificate can be reissued.');
        }

        return DB::transaction(function () use ($certificate, $user, $reasonCode, $reasonText) {
            $old = $certificate;

            $certificate->status = CertificateStatus::Superseded;
            $certificate->save();

            $new = Certificate::create([
                'indigene_id' => $certificate->indigene_id,
                'approved_application_id' => $certificate->approved_application_id,
                'lga_id' => $certificate->lga_id,
                'certificate_number' => null,
                'status' => CertificateStatus::Eligible,
                'public_token_hash' => hash('sha256', Str::random(32)),
                'approved_by' => $user->id,
                'superseded_by_certificate_id' => null,
            ]);

            $new->superseded_by_certificate_id = null;
            $old->superseded_by_certificate_id = $new->id;
            $old->save();

            CertificateStatusEvent::create([
                'certificate_id' => $old->id,
                'from_status' => CertificateStatus::Active->value,
                'to_status' => CertificateStatus::Superseded->value,
                'reason_code' => $reasonCode,
                'reason_text' => $reasonText,
                'effective_at' => now(),
                'actor_id' => $user->id,
                'actor_role' => $user->roles()->first()?->name,
                'actor_lga_id' => $user->activeLga()?->id,
            ]);

            $this->audit->record('certificate.reissued', Certificate::class, $old->id, [
                'status' => CertificateStatus::Active->value,
            ], [
                'status' => CertificateStatus::Superseded->value,
                'new_certificate_id' => $new->id,
            ], 'high', $user);

            return $new;
        });
    }

    /**
     * Re-render a new version for an edited approved record. The certificate keeps
     * its number and active status; the corrected snapshot becomes the current version.
     * The public verification token is preserved so existing printed QR codes stay valid.
     */
    public function refreshForEdit(Certificate $certificate, User $user): CertificateVersion
    {
        $indigene = $certificate->indigene;
        $profile = $indigene->currentProfile;

        if (! $profile) {
            throw new HttpException(409, 'No current profile exists for this indigene.');
        }

        $lga = $certificate->lga;
        $lgaProfile = LgaProfile::where('lga_id', $lga->id)
            ->where('status', 'published')
            ->orderByDesc('version_no')
            ->first();

        $signatory = OfficialSignatory::where('lga_id', $lga->id)
            ->where('status', 'active')
            ->where('is_primary', true)
            ->first();

        if (! $lgaProfile || ! $signatory) {
            throw new HttpException(409, 'LGA branding and an active signatory are required before a certificate can be issued.');
        }

        $number = $certificate->certificate_number ?? $this->numbers->allocate($lga);

        // Preserve the existing verification token so previously printed copies keep
        // a working QR; only fall back to a fresh token for a never-issued certificate.
        $publicToken = null;

        if ($certificate->currentVersion?->snapshot_ciphertext) {
            $previous = json_decode(Crypt::decryptString($certificate->currentVersion->snapshot_ciphertext), true);
            $publicToken = Str::afterLast($previous['verification_url'] ?? '', '/');
        }

        $publicToken = $publicToken ?: Str::random(32);
        $verificationUrl = route('certificates.verify.token', ['token' => $publicToken]);

        $snapshot = $this->buildSnapshot($certificate, $profile, $lgaProfile, $signatory, $number, $verificationUrl);

        return DB::transaction(function () use ($certificate, $profile, $lgaProfile, $signatory, $number, $publicToken, $verificationUrl, $snapshot, $user) {
            $versionNo = CertificateVersion::where('certificate_id', $certificate->id)->max('version_no') + 1;

            $version = CertificateVersion::create([
                'certificate_id' => $certificate->id,
                'version_no' => $versionNo,
                'certificate_template_id' => $snapshot['template_id'],
                'lga_profile_id' => $lgaProfile->id,
                'signatory_id' => $signatory->id,
                'source_profile_id' => $profile->id,
                'snapshot_ciphertext' => Crypt::encryptString(json_encode($snapshot)),
                'qr_payload_hash' => hash('sha256', $verificationUrl),
                'generated_by' => $user->id,
                'generated_at' => now(),
                'status' => 'active',
            ]);

            $certificate->certificate_number = $number;
            $certificate->status = CertificateStatus::Active;
            $certificate->current_version_id = $version->id;
            $certificate->public_token_hash = hash('sha256', $publicToken);
            $certificate->public_token_hint = substr($publicToken, 0, 8);
            $certificate->issued_at = $certificate->issued_at ?? now();
            $certificate->save();

            $pdf = $this->renderPdf($snapshot, $verificationUrl);
            $version->pdf_sha256 = hash('sha256', $pdf);
            $version->save();

            return $version;
        });
    }

    public function buildSnapshot(Certificate $certificate, IndigeneProfile $profile, LgaProfile $lgaProfile, OfficialSignatory $signatory, string $number, ?string $verificationUrl = null): array
    {
        $indigene = $certificate->indigene;
        $lga = $certificate->lga;
        $state = $lga->state;

        return [
            'certificate_id' => $certificate->id,
            'certificate_number' => $number,
            'template_id' => null,
            'holder' => [
                // No title prefix on the certificate: surname, first and middle names only.
                'full_name' => $profile->displayName(),
                'photo_path' => $profile->photoFile?->object_key,
                'sex' => $profile->sex,
                'date_of_birth' => $profile->date_of_birth?->toDateString(),
            ],
            'origin' => [
                'lga' => $lga->name,
                'state' => $state->name,
                'ward' => $profile->ward?->name,
                'unit' => $profile->unit?->name,
                'district' => $profile->district?->name,
            ],
            'registry_number' => $indigene->registry_number,
            'issued_at' => now()->toDateTimeString(),
            // The real token URL is captured so re-rendered copies keep a working QR;
            // the snapshot is encrypted at rest and the raw token stays hashed on the certificate row.
            'verification_url' => $verificationUrl ?? route('certificates.verify.token', ['token' => '__TOKEN__']),
            'branding' => [
                'primary_colour' => $lgaProfile->primary_colour ?? '#087A4B',
                'heading_colour' => $lgaProfile->secondary_colour ?? '#0B1F3A',
                'title_colour' => '#E11414',
                'authority_type' => $lga->type === 'area_council' ? 'AREA COUNCIL' : 'LOCAL GOVERNMENT',
                'assistance_text' => 'Therefore, you may wish to render the holder any possible assistance',
                'certificate_date_format' => 'd/m/Y',
                'show_signatory_name' => true,
                'footer' => $lgaProfile->footer_text ?? 'This certificate is subject to online verification.',
            ],
            'signatory' => [
                'full_name' => $signatory->full_name,
                'office_title' => $signatory->office_title,
                'signature_path' => $signatory->signatureFile?->object_key,
                'seal_path' => $signatory->sealFile?->object_key,
            ],
        ];
    }

    public function renderPdf(array $snapshot, ?string $verificationUrl = null, ?string $copyLabel = null): string
    {
        $snapshot['verification_url'] = $verificationUrl ?? $snapshot['verification_url'];

        // The name line must never carry an honorific prefix, even when the
        // immutable snapshot predates that rule.
        $snapshot['holder']['full_name'] = $this->stripNameTitle($snapshot['holder']['full_name'] ?? '');

        $qrPng = base64_encode($this->renderQrPng($snapshot['verification_url']));

        $photoData = $this->privateAssetBase64($snapshot['holder']['photo_path'] ?? null);
        $signatureData = $this->privateAssetBase64($snapshot['signatory']['signature_path'] ?? null);
        $sealData = $this->privateAssetBase64($snapshot['signatory']['seal_path'] ?? null);

        // DOMPDF with isRemoteEnabled=false does not reliably resolve file:// image
        // paths, so the local certificate artwork is embedded as base64 data URIs.
        $coatOfArmsData = null;
        $coatPath = public_path('images/certificate/nigeria-coat-of-arms.png');

        if (is_file($coatPath)) {
            $coatOfArmsData = base64_encode((string) file_get_contents($coatPath));
        }

        $securityBackgroundData = null;
        $backgroundPath = public_path('images/certificate/certificate-security-background.jpg');

        if (is_file($backgroundPath)) {
            $securityBackgroundData = base64_encode((string) file_get_contents($backgroundPath));
        }

        $pdf = Pdf::loadView('certificates.indigene-certificate', [
            'snapshot' => $snapshot,
            'photoData' => $photoData,
            'qrPng' => $qrPng,
            'copyLabel' => $copyLabel ?? 'ORIGINAL - COPY 01',
            'signatureData' => $signatureData,
            'sealData' => $sealData,
            'coatOfArmsData' => $coatOfArmsData,
            'securityBackgroundData' => $securityBackgroundData,
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Serif',
            'dpi' => 144,
            'chroot' => base_path(),
            'fontDir' => storage_path('fonts'),
            'fontCache' => storage_path('fonts'),
        ]);

        $binary = $pdf->output();

        // Safety net: a certificate PDF that produced no page is a failure, not a success.
        if (substr_count($binary, '/Type /Page') < 1) {
            throw new \RuntimeException('Certificate PDF was not produced.');
        }

        return $binary;
    }

    private function stripNameTitle(string $name): string
    {
        $name = trim($name);

        $titles = [
            'mr', 'mrs', 'ms', 'dr', 'prof', 'chief', 'alhaji', 'hajiya', 'engr', 'barr',
            'hon', 'sen', 'mal', 'alh', 'sheikh', 'mallam', 'haj', 'dame', 'sir', 'mazi', 'nze',
        ];

        foreach ($titles as $title) {
            if (preg_match('/^'.preg_quote($title, '/').'\.?\s+/i', $name)) {
                $name = trim(preg_replace('/^'.preg_quote($title, '/').'\.?\s+/i', '', $name));
                break;
            }
        }

        return $name;
    }

    private function privateAssetBase64(?string $objectKey): ?string
    {
        if (! $objectKey) {
            return null;
        }

        $disk = Storage::disk('private');

        if (! $disk->exists($objectKey)) {
            return null;
        }

        return base64_encode($disk->get($objectKey));
    }

    private function renderQrPng(string $payload): string
    {
        $options = new \chillerlan\QRCode\QROptions([
            'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
            'outputBase64' => false,
            'imageTransparency' => false,
            'scale' => 10,
            'margin' => 1,
            'eccLevel' => \chillerlan\QRCode\Common\EccLevel::M,
        ]);

        return (new \chillerlan\QRCode\QRCode($options))->render($payload);
    }
}
