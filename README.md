# NIMCS - Nigerian Indigene Management and Certification System

A secure, nationwide-ready web platform for registering Nigerian indigenes, routing each
registration through Local Government Area (LGA) approval, issuing verifiable indigene
certificates, and maintaining a permanent audit trail of every approval, certificate version,
download and print occurrence.

Built for **Haigha Tech** on **Laravel 12 + MySQL + Blade + Bootstrap 5** (Trezo template),
implementing the SRD at `../Haigha_Indigene_Management_System_SRD.md`.

---

## Quick start

```bash
# 1. Create the database (XAMPP MariaDB)
mysql -u root -e "CREATE DATABASE haigha_indigene CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Install dependencies
composer install

# 3. Configure .env (APP_NAME, DB_* already defaulted for XAMPP root/no-password)

# 4. Migrate and seed (37 states/FCT, 774 LGAs, roles, permissions, demo users)
php artisan migrate:fresh --seed

# 5. Serve
php artisan serve
```

Open `http://127.0.0.1:8099`.

## Demo accounts

| Role | Email | Password |
|---|---|---|
| System Admin (Haigha) | `admin@haighatech.com` | `Haigha@2026` |
| Data Protection Officer | `dpo@haighatech.com` | `Haigha@2026` |
| LGA Chairman (Damboa, Borno) | `chairman@damboa.ng` | `Haigha@2026` |
| LGA Indigene Officer (Damboa) | `officer@damboa.ng` | `Haigha@2026` |

MFA has been removed from this build; staff sign in with email and password only.
In local development, activation and password-reset links are written to `storage/logs/laravel.log`.

## Roles

- **System Admin** - national scope: users, assignments, master data, approvals (with override rules),
  audit, settings, LGA branding.
- **LGA Chairman** - own LGA: approvals, local geography (wards/units/districts), LGA profile/signatory,
  onboarding (routed to System Admin to preserve separation of duties).
- **LGA Indigene Officer** - own LGA: registration, correction, resubmission; no approvals.
- Optional: Auditor, Print Officer, Data Protection Officer.

## Core workflow

1. **Onboarding** - eight-step wizard (notice, identity/NIN, place of origin, contact, family,
   guardian/next-of-kin, documents, review+declaration) with autosave and server-side validation.
2. **Routing** - Officer submissions route to the Chairman (`pending_chairman`);
   Chairman-created submissions route to the System Admin (`pending_system_admin`) - self-approval is blocked.
3. **Decision** - approve (checklist + password confirmation), request correction, or reject with reason.
   Every decision is immutable and audited. Approval activates certificate eligibility atomically.
4. **Certificate** - issue allocates `{LGA}-{YEAR}-{SEQ}` via a locked sequence, renders a deterministic
   A4 PDF from an encrypted immutable snapshot (DOMPDF + QR), and stores its SHA-256.
5. **Print copies** - each server-authorised printable copy creates one idempotent print event
   (COPY 01 original, COPY 02+ reprint with reason) and a watermarked PDF.
6. **Public verification** - by certificate number or QR token (`/v/{token}`); returns VALID /
   SUSPENDED / SUPERSEDED / REVOKED with minimum attributes only. Rate limited and logged.

## Key security and privacy controls

- **NIN** stored in three representations: encrypted ciphertext, keyed HMAC (exact duplicate block),
  last-4 for masking. Never in URLs, QR, logs, exports or notifications.
- **LGA scoping** derived server-side from active assignments; cross-LGA access returns 403/404.
- **Step-up auth** (password confirmation) for NIN reveal, approvals, revocation, reissue.
- **Append-only audit trail** with hash chaining; sensitive access (reveal/download/export) logged with purpose.
- **Exports** role-scoped, masked, purpose-logged, expiring, formula-injection neutralised.

## Notable directories

```
app/Enums                 Application/certificate/lifecycle statuses
app/Http/Controllers      Public, Auth, Admin + domain controllers
app/Http/Middleware       user.active, password.change, step-up, lga.assignment
app/Models                All SRD Part VI entities (UUIDv7)
app/Policies              Per-entity authorisation incl. separation of duties
app/Services              Workflow, NIN protection, duplicates, numbering, rendering,
                          print events, verification, audit, exports, TOTP, uploads
database/migrations       SRD Part VI schema (UUID PKs everywhere)
database/seeders          Roles/permissions, 37 states + 774 LGAs, demo users, settings
resources/views           Blade views on the Trezo Bootstrap 5 template (light public navbar)
```

## Tests

Smoke/flow suites used during development live outside the repo (PowerShell scripts driving
HTTP sessions). Key scenarios verified: 8-step onboarding, officer→chairman approval,
chairman→admin routing + self-approval block (403), certificate issue/print/reprint/revoke,
public verification VALID/REVOKED, geography import publish, exports, privacy requests,
legal holds, and cross-LGA denial (403).

## Compliance notes

The SRD requires, before production launch: approved DPIA, controller/processor agreement,
DPO contact publication, ROPA, retention schedule, breach playbook, pen-test and DR test
(SRD section 54). This codebase implements the technical controls; legal sign-off remains
with Haigha and the participating authority.

Technology service by [Haigha Tech](https://haighatech.com). This project is not legal advice.
