<?php

namespace Database\Seeders;

use App\Models\Lga;
use App\Models\LgaProfile;
use App\Models\OfficialSignatory;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserLgaAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo staff accounts, LGA branding for the pilot LGA (Damboa, Borno)
 * and default system settings.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@haighatech.com'],
            [
                'full_name' => 'System Administrator',
                'phone' => '+2348000000001',
                'password' => Hash::make('Haigha@2026'),
                'status' => 'active',
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );
        $admin->assignRole('system_admin');

        $dpo = User::firstOrCreate(
            ['email' => 'dpo@haighatech.com'],
            [
                'full_name' => 'Data Protection Officer',
                'phone' => '+2348000000002',
                'password' => Hash::make('Haigha@2026'),
                'status' => 'active',
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );
        $dpo->assignRole('data_protection_officer');

        $damboa = Lga::where('name', 'Damboa')->first();

        if ($damboa) {
            $chairman = User::firstOrCreate(
                ['email' => 'chairman@damboa.ng'],
                [
                    'full_name' => 'Executive Chairman, Damboa LGA',
                    'phone' => '+2348000000003',
                    'password' => Hash::make('Haigha@2026'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'must_change_password' => false,
                ]
            );
            $chairman->assignRole('lga_chairman');

            UserLgaAssignment::firstOrCreate(
                ['user_id' => $chairman->id, 'lga_id' => $damboa->id],
                [
                    'role_id' => \App\Models\Role::where('name', 'lga_chairman')->first()->id,
                    'assignment_type' => 'primary',
                    'appointment_title' => 'Executive Chairman',
                    'starts_at' => now(),
                    'is_primary' => true,
                    'status' => 'active',
                    'created_by' => $admin->id,
                ]
            );

            $officer = User::firstOrCreate(
                ['email' => 'officer@damboa.ng'],
                [
                    'full_name' => 'Indigene Officer, Damboa LGA',
                    'phone' => '+2348000000004',
                    'password' => Hash::make('Haigha@2026'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'must_change_password' => false,
                ]
            );
            $officer->assignRole('lga_indigene_officer');

            UserLgaAssignment::firstOrCreate(
                ['user_id' => $officer->id, 'lga_id' => $damboa->id],
                [
                    'role_id' => \App\Models\Role::where('name', 'lga_indigene_officer')->first()->id,
                    'assignment_type' => 'primary',
                    'appointment_title' => 'Indigene Officer',
                    'starts_at' => now(),
                    'is_primary' => true,
                    'status' => 'active',
                    'created_by' => $admin->id,
                ]
            );

            // Pilot LGA branding and signatory (SRD 34) so certificate
            // issuance is enabled for the demo LGA.
            LgaProfile::firstOrCreate(
                ['lga_id' => $damboa->id, 'version_no' => 1],
                [
                    'display_name' => 'Damboa Local Government Council',
                    'office_address' => 'Local Government Secretariat, Damboa, Borno State',
                    'support_phone' => '+2348000000005',
                    'support_email' => 'support@damboa.ng',
                    'primary_colour' => '#087A4B',
                    'secondary_colour' => '#0B1F3A',
                    'certificate_heading' => 'DAMBOA LOCAL GOVERNMENT, BORNO STATE',
                    'footer_text' => 'This certificate is subject to verification using the certificate number or QR code shown below.',
                    'status' => 'published',
                    'effective_from' => now()->toDateString(),
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                ]
            );

            OfficialSignatory::firstOrCreate(
                ['lga_id' => $damboa->id, 'full_name' => 'Alhaji Mustapha Abdul'],
                [
                    'office_title' => 'Executive Chairman, Damboa Local Government',
                    'appointment_reference' => 'DAM/CH/2026/001',
                    'effective_from' => now()->toDateString(),
                    'status' => 'active',
                    'is_primary' => true,
                    'created_by' => $admin->id,
                    'approved_by' => $admin->id,
                ]
            );

            // Pilot wards and villages for Damboa so onboarding can run end to end.
            $this->seedPilotGeography($damboa);
        }

        $this->seedSettings($admin);
    }

    private function seedPilotGeography(Lga $lga): void
    {
        $wardNames = [
            'Ajigin', 'Bulabulin', 'Damboa Central', 'Gumsuri', 'Kafa',
            'Koleri', 'Mafi', 'Misakurbudu', 'Molai Kubti', 'Nzuda Wuyaram',
        ];

        $villages = [
            'Ajigin', 'Kuboa', 'Kwaramu', 'Gomari',
            'Kaltaram', 'Mulgwi', 'Sabon Gari', 'Gargam',
            'Kawarmari', 'Ngwalimiri', 'Chongolo', 'Kwaya',
        ];

        foreach ($wardNames as $index => $name) {
            $ward = \App\Models\Ward::firstOrCreate(
                ['lga_id' => $lga->id, 'code' => 'DBW-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)],
                [
                    'name' => $name,
                    'status' => 'active',
                    'source_name' => 'LGA sign-off (pilot)',
                    'created_by' => User::first()->id,
                ]
            );

            foreach (array_slice($villages, 0, 4) as $vi => $village) {
                \App\Models\Unit::firstOrCreate(
                    ['ward_id' => $ward->id, 'category' => 'village', 'code' => $ward->code.'-V'.($vi + 1)],
                    [
                        'lga_id' => $lga->id,
                        'name' => $village,
                        'status' => 'active',
                        'source_name' => 'LGA sign-off (pilot)',
                        'created_by' => User::first()->id,
                    ]
                );
            }
        }
    }

    private function seedSettings(User $admin): void
    {
        $defaults = [
            'org_name' => 'Nigerian Indigene Management and Certification System',
            'org_provider_name' => 'Haigha Tech',
            'org_short_name' => 'NIMCS',
            'org_support_email' => 'support@haighatech.com',
            'org_support_phone' => '+234 000 000 0000',
            'meta_description' => 'Register and verify approved Nigerian indigene certificates securely.',
            'meta_keywords' => 'indigene certificate, LGA approval, certificate verification, Nigeria',
            'meta_author' => 'Haigha Tech',
            'meta_og_title' => 'Nigerian Indigene Management and Certification System',
            'meta_og_description' => 'Register indigenes through the authorised LGA workflow and verify issued certificates instantly.',
            'auth_session_idle_minutes' => '30',
            'auth_session_max_hours' => '8',
            'auth_trusted_device_days' => '0',
            'application_due_days' => '7',
            'application_require_nin' => '1',
            'application_plausible_age_min' => '0',
            'application_plausible_age_max' => '120',
            'ninauth_enabled' => '0',
            'ninauth_provider_name' => 'NIMC NINAuth (pending onboarding)',
            'documents_max_size_mb' => '10',
            'documents_required_min' => '1',
            'certificate_expiry_enabled' => '0',
            'certificate_validity_years' => '0',
            'certificate_number_padding' => '6',
            'notify_email_enabled' => '1',
            'notify_sms_enabled' => '0',
            'notify_digest_hour' => '08',
            'retention_verification_events_days' => '30',
            'retention_audit_days' => '2555',
            'retention_exports_days' => '1',
            'public_verification_show_photo' => '0',
            'verification_rate_limit_per_ip' => '30',
        ];

        foreach ($defaults as $key => $value) {
            SystemSetting::firstOrCreate(
                ['key' => $key, 'scope_type' => 'global'],
                ['value' => $value, 'updated_by' => $admin->id]
            );
        }
    }
}
