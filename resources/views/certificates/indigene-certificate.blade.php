@php
    /*
    |--------------------------------------------------------------------------
    | DOMPDF-safe certificate variables
    |--------------------------------------------------------------------------
    | Keep presentation logic small and deterministic. CertificateRenderService
    | should already have created an immutable snapshot and base64 image data.
    */
    $value = static fn (string $path, $default = '') => data_get($snapshot, $path, $default);

    $safeHex = static function ($candidate, string $fallback): string {
        return is_string($candidate) && preg_match('/^#[0-9a-fA-F]{6}$/', $candidate)
            ? strtoupper($candidate)
            : $fallback;
    };

    $asDataUri = static function ($data, string $mime): ?string {
        if (! is_string($data) || trim($data) === '') {
            return null;
        }

        return str_starts_with($data, 'data:')
            ? $data
            : "data:{$mime};base64,{$data}";
    };

    $primaryColour = $safeHex($value('branding.primary_colour'), '#087A4B');
    $headingColour = $safeHex($value('branding.heading_colour'), '#006178');
    $titleColour = $safeHex($value('branding.title_colour'), '#E11414');

    $holderName = trim((string) $value('holder.full_name'));
    $lgaName = trim((string) $value('origin.lga'));
    $stateName = trim((string) $value('origin.state'));
    $wardName = trim((string) $value('origin.ward'));
    $unitName = trim((string) $value('origin.unit'));
    $districtName = trim((string) $value('origin.district'));

    $authorityType = trim((string) $value('branding.authority_type', 'LOCAL GOVERNMENT'));
    $authorityHeading = trim((string) $value(
        'branding.authority_heading',
        mb_strtoupper("{$lgaName} {$authorityType}")
    ));

    $isFct = in_array(mb_strtoupper($stateName), [
        'FCT',
        'FEDERAL CAPITAL TERRITORY',
    ], true);

    $stateHeading = trim((string) $value(
        'branding.state_heading',
        $isFct
            ? 'FEDERAL CAPITAL TERRITORY'
            : mb_strtoupper($stateName).' STATE'
    ));

    $unitNeedsSuffix = $unitName !== ''
        && ! str_contains(mb_strtolower($unitName), 'unit');

    $photoSrc = $asDataUri($photoData ?? null, 'image/jpeg');
    $qrSrc = $asDataUri($qrPng ?? null, 'image/png');
    $signatureSrc = $asDataUri($signatureData ?? null, 'image/png');
    $sealSrc = $asDataUri($sealData ?? null, 'image/png');
    $backgroundPath = public_path('images/certificate/certificate-security-background.jpg');
    $coatPath = public_path('images/certificate/nigeria-coat-of-arms.png');
    $backgroundSrc = $asDataUri($securityBackgroundData ?? null, 'image/jpeg')
        ?? (is_file($backgroundPath) ? 'file://'.$backgroundPath : null);
    $coatSrc = $asDataUri($coatOfArmsData ?? null, 'image/png')
        ?? (is_file($coatPath) ? 'file://'.$coatPath : null);

    $certificateNumber = trim((string) $value('certificate_number'));
    $registryNumber = trim((string) $value('registry_number'));
    $issueDate = \Carbon\Carbon::parse($value('issued_at'))->format(
        (string) $value('branding.certificate_date_format', 'd/m/y')
    );

    $signatoryName = trim((string) $value('signatory.full_name'));
    $officeTitle = trim((string) $value('signatory.office_title', 'Executive Chairman'));
    $showSignatoryName = (bool) $value('branding.show_signatory_name', false);
    $copyText = trim((string) ($copyLabel ?? 'ORIGINAL - COPY 01'));
    $isReprint = str_contains(mb_strtoupper($copyText), 'REPRINT');

    $nameClass = mb_strlen($holderName) > 38
        ? 'holder-name holder-name--small'
        : (mb_strlen($holderName) > 28
            ? 'holder-name holder-name--medium'
            : 'holder-name');

    $authorityClass = mb_strlen($authorityHeading) > 34
        ? 'authority-heading authority-heading--small'
        : 'authority-heading';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Indigene Certificate - {{ $certificateNumber }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 210mm;
            height: 297mm;
            margin: 0;
            padding: 0;
        }

        body {
            overflow: hidden;
            color: #101714;
            background: #edf6f1;
            font-family: "DejaVu Serif", serif;
        }

        /*
         * Use 296.6mm instead of 297mm to avoid DOMPDF rounding the final
         * fraction of a point onto a second page.
         */
        .page {
            position: relative;
            width: 210mm;
            height: 296.6mm;
            overflow: hidden;
            page-break-before: avoid;
            page-break-after: avoid;
            page-break-inside: avoid;
            background: #edf6f1;
        }

        .security-background {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 0;
            width: 210mm;
            height: 296.6mm;
        }

        /* Visible fallback if the supplied background image is unavailable. */
        .frame-outer,
        .frame-middle,
        .frame-inner {
            position: absolute;
            z-index: 1;
            pointer-events: none;
        }

        .frame-outer {
            top: 3mm;
            right: 3mm;
            bottom: 3mm;
            left: 3mm;
            border: .35mm solid {{ $primaryColour }};
        }

        .frame-middle {
            top: 5mm;
            right: 5mm;
            bottom: 5mm;
            left: 5mm;
            border: .65mm double {{ $primaryColour }};
            opacity: .45;
        }

        .frame-inner {
            top: 8mm;
            right: 8mm;
            bottom: 8mm;
            left: 8mm;
            border: .2mm solid {{ $primaryColour }};
            opacity: .28;
        }

        .certificate-number {
            position: absolute;
            top: 16.5mm;
            right: 20mm;
            z-index: 5;
            width: 68mm;
            color: #111;
            font-size: 12.5pt;
            font-weight: bold;
            line-height: 1.2;
            text-align: right;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        .certificate-number .copy-label {
            display: block;
            margin-top: 1.2mm;
            color: #45534d;
            font-size: 7.2pt;
            font-weight: normal;
            letter-spacing: .08em;
        }

        .coat-of-arms {
            position: absolute;
            top: 22.5mm;
            left: 92.5mm;
            z-index: 5;
            width: 25mm;
            height: 21mm;
        }

        .authority-heading,
        .state-heading,
        .certificate-title,
        .certify-label,
        .holder-name,
        .origin-line,
        .unit-line,
        .assistance-line {
            position: absolute;
            z-index: 5;
            right: 18mm;
            left: 18mm;
            text-align: center;
        }

        .authority-heading {
            top: 63mm;
            color: {{ $headingColour }};
            font-size: 25pt;
            font-weight: bold;
            line-height: 1.1;
            letter-spacing: .015em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .authority-heading--small {
            top: 64mm;
            font-size: 21pt;
        }

        .state-heading {
            top: 80mm;
            color: #101010;
            font-size: 18pt;
            font-weight: bold;
            line-height: 1.1;
            text-transform: uppercase;
        }

        .certificate-title {
            top: 94mm;
            right: 48mm;
            left: 28mm;
            color: {{ $titleColour }};
            font-size: 28pt;
            font-style: italic;
            font-weight: bold;
            line-height: 1.1;
        }

        .certify-label {
            top: 109.5mm;
            right: 48mm;
            left: 28mm;
            color: #111;
            font-size: 12.5pt;
            font-style: italic;
            line-height: 1.2;
        }

        .holder-name {
            top: 121.5mm;
            right: 50mm;
            left: 25mm;
            color: #111;
            font-size: 20pt;
            font-style: italic;
            line-height: 1.15;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .holder-name--medium {
            top: 122.5mm;
            font-size: 17.5pt;
        }

        .holder-name--small {
            top: 123mm;
            font-size: 15pt;
        }

        .photo {
            position: absolute;
            top: 75.5mm;
            right: 25mm;
            z-index: 7;
            width: 27mm;
            height: 37mm;
            overflow: hidden;
            background: #fff;
        }

        .photo img {
            width: 27mm;
            height: 37mm;
            border: 0;
        }

        .photo-placeholder {
            width: 27mm;
            height: 37mm;
            padding-top: 16mm;
            border: .3mm solid #b4c6bd;
            color: #66746e;
            background: #f4f7f6;
            font-size: 7pt;
            text-align: center;
        }

        .origin-line {
            top: 148mm;
            color: #111;
            font-size: 14.2pt;
            font-style: italic;
            line-height: 1.3;
        }

        .unit-line {
            top: 160mm;
            color: #111;
            font-size: 14.2pt;
            font-style: italic;
            line-height: 1.3;
        }

        .assistance-line {
            top: 178mm;
            right: 24mm;
            left: 24mm;
            color: #111;
            font-size: 13.8pt;
            font-style: italic;
            line-height: 1.35;
        }

        .qr-block {
            position: absolute;
            top: 236mm;
            left: 18mm;
            z-index: 6;
            width: 40mm;
            text-align: center;
        }

        .qr-block img,
        .qr-placeholder {
            width: 38mm;
            height: 38mm;
            border: 0;
        }

        .qr-placeholder {
            padding-top: 17mm;
            border: .3mm solid #98aca2;
            color: #66746e;
            background: #fff;
            font-size: 7pt;
        }

        .qr-caption {
            margin-top: 1mm;
            color: #26332d;
            font-size: 7.5pt;
            font-weight: bold;
        }

        .signature-block {
            position: absolute;
            top: 258mm;
            right: 22mm;
            z-index: 6;
            width: 62mm;
            color: #111;
            text-align: center;
        }

        .issue-date {
            height: 4mm;
            font-size: 10pt;
            line-height: 1;
            text-align: right;
        }

        .signature-area {
            position: relative;
            height: 2.5mm;
            border-bottom: .35mm solid #222;
        }

        .signature-image {
            position: absolute;
            right: 12mm;
            bottom: .5mm;
            width: 38mm;
            height: 14mm;
        }

        .seal-image {
            position: absolute;
            right: -2mm;
            bottom: -8mm;
            width: 20mm;
            height: 20mm;
            opacity: .88;
        }

        .signatory-name {
            margin-top: 1.2mm;
            font-size: 9.5pt;
            font-weight: bold;
            line-height: 1.15;
        }

        .office-title {
            margin-top: 1.1mm;
            font-size: 12pt;
            line-height: 1.1;
        }

        .signature-label {
            margin-top: .9mm;
            font-size: 8.5pt;
            line-height: 1.1;
        }

        .footer-micro {
            position: absolute;
            right: 60mm;
            bottom: 6.3mm;
            left: 60mm;
            z-index: 6;
            overflow: hidden;
            color: #607069;
            font-size: 6.3pt;
            line-height: 1.15;
            text-align: center;
            white-space: nowrap;
        }

        .reprint-watermark {
            position: absolute;
            top: 211mm;
            right: 55mm;
            left: 55mm;
            z-index: 4;
            color: rgba(130, 26, 26, .20);
            font-size: 17pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: .16em;
        }
    </style>
</head>
<body>
    <div class="page">
        @if ($backgroundSrc)
            <img class="security-background" src="{{ $backgroundSrc }}" alt="">
        @else
            <div class="frame-outer"></div>
            <div class="frame-middle"></div>
            <div class="frame-inner"></div>
        @endif

        <div class="certificate-number">
            {{ $certificateNumber }}
        </div>

        @if ($coatSrc)
            <img class="coat-of-arms" src="{{ $coatSrc }}" alt="">
        @endif

        <div class="{{ $authorityClass }}">{{ $authorityHeading }}</div>
        <div class="state-heading">{{ $stateHeading }}</div>

        <div class="certificate-title">Indigene Certificate</div>
        <div class="certify-label">I certify that</div>
        <div class="{{ $nameClass }}">{{ $holderName }}</div>

        <div class="photo">
            @if ($photoSrc)
                <img src="{{ $photoSrc }}" alt="">
            @else
                <div class="photo-placeholder">Holder photograph</div>
            @endif
        </div>

        <div class="origin-line">
            is a Bona fide indigene of
            <strong>{{ $lgaName }} Local Government Area</strong>
        </div>

        <div class="unit-line">
            from <strong>{{ mb_strtoupper($unitName) }}</strong>
            @if ($unitNeedsSuffix)
                Village Unit
            @endif
            @if ($districtName !== '')
                of <strong>{{ $districtName }}</strong> District
            @elseif ($wardName !== '')
                of <strong>{{ $wardName }}</strong> Ward
            @endif
        </div>

        <div class="assistance-line">
            {{ $value(
                'branding.assistance_text',
                'Therefore, you may wish to render the holder any possible assistance'
            ) }}
        </div>

        <div class="qr-block">
            @if ($qrSrc)
                <img src="{{ $qrSrc }}" alt="">
            @else
                <div class="qr-placeholder">QR CODE</div>
            @endif
            <div class="qr-caption">Scan to verify</div>
        </div>

        <div class="signature-block">
            <div class="issue-date">{{ $issueDate }}</div>
            <div class="signature-area">
                @if ($signatureSrc)
                    <img class="signature-image" src="{{ $signatureSrc }}" alt="">
                @endif
        @if ($sealSrc)
            <img class="seal-image" src="{{ $sealSrc }}" alt="">
        @endif
            </div>
            @if ($showSignatoryName && $signatoryName !== '')
                <div class="signatory-name">{{ $signatoryName }}</div>
            @endif
            <div class="office-title">{{ $officeTitle }}</div>
            <div class="signature-label">(Signature &amp; Seal)</div>
        </div>

        <div class="footer-micro">
            {{ $registryNumber }} &middot;
            Verify at {{ parse_url(config('app.url'), PHP_URL_HOST) }} &middot;
            Technology by Haigha Tech
        </div>
    </div>
</body>
</html>
