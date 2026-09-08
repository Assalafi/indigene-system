<?php

namespace Tests\Feature;

use Tests\TestCase;

class CertificateTemplateTest extends TestCase
{
    public function test_footer_matches_the_reference_without_personal_or_promotional_details(): void
    {
        $html = view('certificates.indigene-certificate', [
            'snapshot' => [
                'certificate_number' => 'DAM-2026-000029',
                'registry_number' => 'REG-000029',
                'issued_at' => '2026-09-08 12:00:00',
                'holder' => ['full_name' => 'ABDULRAHAMAN MUSTAPHA'],
                'origin' => [
                    'lga' => 'Damboa',
                    'state' => 'Borno',
                    'ward' => 'Gumsuri',
                    'unit' => 'Garjang',
                    'district' => 'Gumsuri',
                ],
                'branding' => [
                    'show_signatory_name' => true,
                ],
                'signatory' => [
                    'full_name' => 'UNWANTED CHAIRMAN NAME',
                    'office_title' => 'Executive Chairman',
                ],
            ],
            'photoData' => null,
            'qrPng' => null,
            'copyLabel' => 'ORIGINAL - COPY 01',
            'signatureData' => base64_encode('unused signature'),
            'sealData' => base64_encode('unused seal'),
            'coatOfArmsData' => null,
            'securityBackgroundData' => null,
        ])->render();

        $this->assertStringContainsString('Executive Chairman', $html);
        $this->assertStringContainsString('(Signature &amp; Seal)', $html);
        $this->assertStringNotContainsString('UNWANTED CHAIRMAN NAME', $html);
        $this->assertStringNotContainsString('REG-000029', $html);
        $this->assertStringNotContainsString('Technology by Haigha Tech', $html);
        $this->assertStringNotContainsString('Verify at', $html);
        $this->assertStringNotContainsString('Scan to verify', $html);
        $this->assertStringNotContainsString('class="signature-image"', $html);
        $this->assertStringNotContainsString('class="seal-image"', $html);
    }
}
