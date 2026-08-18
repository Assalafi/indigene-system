<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * SRD 13 - role and permission model.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            ['name' => 'application.view', 'description' => 'View applications within authorised scope'],
            ['name' => 'application.create', 'description' => 'Create and edit draft applications'],
            ['name' => 'application.edit-delegated', 'description' => 'Edit another user draft when delegated'],
            ['name' => 'application.submit', 'description' => 'Submit applications for review'],
            ['name' => 'application.decide', 'description' => 'Approve, reject or request corrections'],
            ['name' => 'application.review-duplicates', 'description' => 'Review and resolve duplicate flags'],
            ['name' => 'application.export', 'description' => 'Export application data (masked)'],
            ['name' => 'indigene.view', 'description' => 'View approved indigene records'],
            ['name' => 'indigene.amend', 'description' => 'Start amendment applications'],
            ['name' => 'indigene.reveal-nin', 'description' => 'Reveal full NIN (privileged, audited)'],
            ['name' => 'indigene.suspend', 'description' => 'Suspend an indigene record'],
            ['name' => 'indigene.export', 'description' => 'Export indigene data (masked)'],
            ['name' => 'certificate.view', 'description' => 'View certificates and print history'],
            ['name' => 'certificate.issue', 'description' => 'Issue certificates for approved records'],
            ['name' => 'certificate.print', 'description' => 'Generate authorised print copies'],
            ['name' => 'certificate.manage-status', 'description' => 'Suspend, reinstate, revoke or reissue certificates'],
            ['name' => 'certificate.export', 'description' => 'Export certificate data'],
            ['name' => 'geography.view', 'description' => 'View geography master data'],
            ['name' => 'geography.manage-local', 'description' => 'Add/edit districts, wards and units in own LGA'],
            ['name' => 'geography.manage-national', 'description' => 'Manage states and LGAs nationally'],
            ['name' => 'geography.import', 'description' => 'Import and publish geography datasets'],
            ['name' => 'user.manage', 'description' => 'Create, edit, suspend and assign staff users'],
            ['name' => 'report.view', 'description' => 'View operational reports within scope'],
            ['name' => 'report.export', 'description' => 'Export role-safe reports'],
            ['name' => 'report.export-national', 'description' => 'Export national-scope reports'],
            ['name' => 'document.view', 'description' => 'View supporting documents within scope'],
            ['name' => 'audit.view', 'description' => 'View the full audit log'],
            ['name' => 'audit.view-lga', 'description' => 'View own-LGA action log'],
            ['name' => 'audit.view-own', 'description' => 'View own audit actions'],
            ['name' => 'privacy.manage', 'description' => 'Manage privacy requests and legal holds'],
            ['name' => 'privacy.view', 'description' => 'View privacy compliance records'],
            ['name' => 'lga-profile.manage', 'description' => 'Publish LGA branding and signatories'],
            ['name' => 'lga-profile.view', 'description' => 'View LGA branding and signatories'],
            ['name' => 'settings.view', 'description' => 'View system settings'],
            ['name' => 'settings.manage', 'description' => 'Update system settings'],
            ['name' => 'fraud.view', 'description' => 'View fraud reports'],
            ['name' => 'fraud.manage', 'description' => 'Resolve fraud reports'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name'], 'guard_name' => 'web'], $permission);
        }

        $systemAdmin = Role::firstOrCreate(['name' => 'system_admin', 'guard_name' => 'web'], [
            'description' => 'Haigha-authorised platform administrator with national scope',
        ]);
        $systemAdmin->syncPermissions(Permission::all());

        $chairman = Role::firstOrCreate(['name' => 'lga_chairman', 'guard_name' => 'web'], [
            'description' => 'LGA Chairman: approval, local geography and LGA branding authority',
        ]);
        $chairman->syncPermissions([
            'application.view', 'application.create', 'application.edit-delegated', 'application.submit',
            'application.decide', 'application.review-duplicates', 'application.export',
            'indigene.view', 'indigene.amend', 'indigene.suspend', 'indigene.export',
            'certificate.view', 'certificate.issue', 'certificate.print', 'certificate.manage-status', 'certificate.export',
            'geography.view', 'geography.manage-local',
            'report.view', 'report.export',
            'document.view',
            'audit.view-lga',
            'lga-profile.manage', 'lga-profile.view',
            'fraud.view',
        ]);

        $officer = Role::firstOrCreate(['name' => 'lga_indigene_officer', 'guard_name' => 'web'], [
            'description' => 'LGA Indigene Officer: registration and management in one assigned LGA',
        ]);
        $officer->syncPermissions([
            'application.view', 'application.create', 'application.submit',
            'indigene.view',
            'certificate.view', 'certificate.print',
            'geography.view', 'geography.manage-local',
            'report.view', 'report.export',
            'document.view',
            'audit.view-own',
        ]);

        $auditor = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web'], [
            'description' => 'Read-only evidence reviewer; export disabled by default',
        ]);
        $auditor->syncPermissions([
            'application.view', 'indigene.view', 'certificate.view', 'geography.view',
            'report.view', 'audit.view-lga', 'document.view', 'fraud.view',
        ]);

        $printOfficer = Role::firstOrCreate(['name' => 'print_officer', 'guard_name' => 'web'], [
            'description' => 'Can generate print copies of approved certificates only',
        ]);
        $printOfficer->syncPermissions([
            'certificate.view', 'certificate.print', 'indigene.view', 'application.view',
        ]);

        $dpo = Role::firstOrCreate(['name' => 'data_protection_officer', 'guard_name' => 'web'], [
            'description' => 'Privacy request and compliance access',
        ]);
        $dpo->syncPermissions([
            'privacy.manage', 'privacy.view', 'indigene.view', 'audit.view-lga',
            'report.view', 'document.view', 'fraud.view',
        ]);
    }
}
