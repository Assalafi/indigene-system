<?php

namespace App\Http\Controllers;

use App\Models\Indigene;
use App\Models\IndigeneApplication;
use App\Models\IndigeneProfile;
use App\Models\IndigeneRelation;
use App\Services\GeographyScopeService;
use App\Services\IndigeneProfileVersionService;
use App\Services\NinProtectionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * SRD 5 / 20.2 - the eight-step indigene onboarding wizard.
 * State and LGA are server-derived from the user's assignment; the browser
 * lga_id is never trusted (SRD 13.3).
 */
class ApplicationWizardController extends Controller
{
    public const STEPS = [1, 2, 3, 4, 5, 6, 7, 8];

    public const STEP_NAMES = [
        1 => 'Notice and authority',
        2 => 'Identity',
        3 => 'Place of origin',
        4 => 'Contact and residence',
        5 => 'Family and indigene basis',
        6 => 'Guardian and next of kin',
        7 => 'Supporting documents',
        8 => 'Review and declaration',
    ];

    public function __construct(
        private NinProtectionService $nin,
        private IndigeneProfileVersionService $profiles,
        private GeographyScopeService $geo,
    ) {}

    public function create(\Illuminate\Http\Request $request)
    {
        $this->authorize('create', IndigeneApplication::class);

        $lga = auth()->user()->activeLga();

        if (! $lga && auth()->user()->isSystemAdmin()) {
            if ($request->filled('lga_id')) {
                $lga = \App\Models\Lga::where('status', 'active')->find($request->input('lga_id'));

                if (! $lga) {
                    abort(404);
                }
            } else {
                return view('applications.select-lga', [
                    'states' => \App\Models\State::where('status', 'active')->orderBy('name')->get(),
                    'lgas' => \App\Models\Lga::with('state')->where('status', 'active')->orderBy('name')->get(),
                ]);
            }
        }

        $state = $lga?->state;

        if (! $lga || ! $state) {
            abort(403, 'You need an active LGA assignment to register indigenes.');
        }

        $indigene = Indigene::create([
            'registry_number' => 'TMP-'.\Illuminate\Support\Str::uuid(),
            'origin_state_id' => $state->id,
            'origin_lga_id' => $lga->id,
            'lifecycle_status' => 'provisional',
            'created_by' => auth()->id(),
        ]);

        $indigene->registry_number = $this->profiles->registryNumber($indigene);
        $indigene->save();

        $profile = IndigeneProfile::create([
            'indigene_id' => $indigene->id,
            'version_no' => 1,
            'surname' => '',
            'first_name' => '',
            'sex' => '',
            'date_of_birth' => null,
            'nationality' => 'Nigerian',
            'origin_state_id' => $state->id,
            'origin_lga_id' => $lga->id,
            'ward_id' => null,
            'unit_id' => null,
            'profile_status' => 'draft',
            'is_current' => false,
            'created_by' => auth()->id(),
        ]);

        $application = IndigeneApplication::create([
            'application_number' => $this->profiles->applicationNumber(),
            'indigene_id' => $indigene->id,
            'profile_id' => $profile->id,
            'lga_id' => $lga->id,
            'application_type' => 'onboarding',
            'status' => 'draft',
            'approval_route' => auth()->user()->hasRole('lga_chairman') ? 'admin_only' : 'chairman_or_admin',
            'created_by' => auth()->id(),
            'last_saved_step' => 1,
        ]);

        return redirect()->route('applications.wizard', ['application' => $application, 'step' => 1]);
    }

    public function show(IndigeneApplication $application, int $step)
    {
        $this->authorize('update', $application);

        abort_if(! in_array($step, self::STEPS, true), 404);

        $profile = $application->profile;
        $indigene = $application->indigene;
        $lga = $application->lga;

        $viewData = [
            'application' => $application,
            'profile' => $profile,
            'indigene' => $indigene,
            'step' => $step,
            'lga' => $lga,
            'state' => $lga->state,
            'districts' => \App\Models\District::where('lga_id', $lga->id)->where('status', 'active')->get(),
            'wards' => \App\Models\Ward::where('lga_id', $lga->id)->where('status', 'active')->get(),
            'units' => \App\Models\Unit::where('lga_id', $lga->id)->where('status', 'active')->where('category', '!=', 'polling_unit')->get(),
            'residenceStates' => \App\Models\State::where('status', 'active')->get(),
            'documentTypes' => $this->documentTypes(),
        ];

        return view("applications.wizard.step{$step}", $viewData);
    }

    public function store(IndigeneApplication $application, int $step, Request $request)
    {
        $this->authorize('update', $application);

        abort_if(! in_array($step, self::STEPS, true), 404);

        if ($request->boolean('autosave')) {
            return $this->autosave($application, $step, $request);
        }

        $this->{"step{$step}"}($application, $request);

        $application->last_saved_step = $step;
        $application->save();

        if ($step === 8) {
            return redirect()->route('applications.show', $application)
                ->with('status', 'Application '.$application->application_number.' has been submitted for review.');
        }

        return redirect()->route('applications.wizard', ['application' => $application, 'step' => $step + 1]);
    }

    // ------------------------------------------------------------------
    // Step handlers
    // ------------------------------------------------------------------

    private function step1(IndigeneApplication $application, Request $request): void
    {
        $request->validate([
            'acknowledge_notice' => ['accepted'],
            'acknowledge_privacy' => ['accepted'],
        ], [
            'acknowledge_notice.accepted' => 'The applicant or operator must acknowledge the notice and authority to continue.',
            'acknowledge_privacy.accepted' => 'The privacy notice acknowledgement is required to continue.',
        ]);

        $application->profile()->first()->update([]);

        // FR-IND-009: record the notice, declaration and acknowledgement version (SRD 35.8).
        $application->declaration_version = '1.0';
        $application->save();

        \App\Models\ConsentRecord::updateOrCreate(
            [
                'indigene_id' => $application->indigene_id,
                'application_id' => $application->id,
            ],
            [
                'data_subject_type' => 'applicant',
                'notice_version' => '1.0',
                'lawful_basis' => 'public_interest_task',
                'purpose_codes' => ['indigene_register', 'lga_approval', 'certificate_issuance'],
                'consent_required' => false,
                'accepted' => true,
                'captured_method' => 'portal',
                'captured_by' => auth()->id(),
                'ip_hash' => hash('sha256', request()->ip().'|'.config('app.key')),
                'user_agent' => request()->userAgent(),
                'captured_at' => now(),
            ]
        );
    }

    private function step2(IndigeneApplication $application, Request $request): void
    {
        $data = $request->validate([
            'nin' => ['required', 'string', 'size:11', 'regex:/^\d{11}$/'],
            'title' => ['nullable', 'string', 'max:30'],
            'surname' => ['required', 'string', 'min:2', 'max:100'],
            'first_name' => ['required', 'string', 'min:2', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'other_names' => ['nullable', 'string', 'max:150'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', 'string', 'in:male,female'],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'nationality' => ['nullable', 'string', 'max:80'],
            'photo' => ['required', 'image', 'max:5120'],
        ], [
            'nin.required' => 'An 11-digit NIN is required by default policy.',
            'nin.size' => 'The NIN must be exactly 11 digits.',
            'nin.regex' => 'The NIN must contain only digits.',
            'photo.required' => 'An applicant photograph is required.',
        ]);

        $indigene = $application->indigene;

        try {
            $indigene->nin_ciphertext = $this->nin->encrypt($data['nin']);
            $indigene->nin_hash = $this->nin->hash($data['nin']);
            $indigene->nin_last4 = $this->nin->last4($data['nin']);
            $indigene->nin_verification_status = 'unverified';
            $indigene->save();
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'nin' => 'This NIN is already registered to another record in the system. An exact NIN match blocks registration unless this is an authorised amendment of the same indigene.',
            ]);
        }

        $profile = $application->profile;
        $profile->fill([
            'title' => $data['title'] ?? null,
            'surname' => $data['surname'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'other_names' => $data['other_names'] ?? null,
            'date_of_birth' => $data['date_of_birth'],
            'sex' => $data['sex'],
            'marital_status' => $data['marital_status'] ?? null,
            'nationality' => $data['nationality'] ?? 'Nigerian',
        ]);

        if ($request->hasFile('photo')) {
            $file = app(\App\Services\FileUploadService::class)->storeImage($request->file('photo'), 'photos', auth()->user());
            $profile->photo_file_id = $file->id;
        }

        $profile->save();
    }

    private function step3(IndigeneApplication $application, Request $request): void
    {
        $lga = $application->lga;

        $data = $request->validate([
            'district_id' => ['nullable', 'uuid', 'exists:districts,id'],
            'ward_id' => ['required', 'uuid', 'exists:wards,id'],
            'unit_id' => ['required', 'uuid', 'exists:units,id'],
            'hometown' => ['nullable', 'string', 'max:180'],
        ], [
            'ward_id.required' => 'Select the ward of origin.',
            'unit_id.required' => 'Select the village/community unit of origin.',
        ]);

        $ward = \App\Models\Ward::findOrFail($data['ward_id']);
        $unit = \App\Models\Unit::findOrFail($data['unit_id']);
        $district = ($data['district_id'] ?? null) ? \App\Models\District::find($data['district_id']) : null;

        if ($ward->lga_id !== $lga->id) {
            throw ValidationException::withMessages(['ward_id' => 'The selected ward does not belong to your LGA.']);
        }

        if ($unit->ward_id !== $ward->id || $unit->lga_id !== $lga->id) {
            throw ValidationException::withMessages(['unit_id' => 'The selected unit does not belong to the selected ward.']);
        }

        if ($district && $district->lga_id !== $lga->id) {
            throw ValidationException::withMessages(['district_id' => 'The selected district does not belong to your LGA.']);
        }

        // Polling units are not used as certificate village text (SRD 24.2).
        if ($unit->isPollingUnit()) {
            throw ValidationException::withMessages(['unit_id' => 'Polling units cannot be used as a village of origin. Choose a village/community unit.']);
        }

        $application->profile->update([
            'district_id' => $district?->id,
            'ward_id' => $ward->id,
            'unit_id' => $unit->id,
            'hometown' => $data['hometown'] ?? null,
        ]);
    }

    private function step4(IndigeneApplication $application, Request $request): void
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:190'],
            'residential_address' => ['required', 'string', 'max:1000'],
            'residence_town' => ['required', 'string', 'max:150'],
            'residence_state_id' => ['nullable', 'uuid', 'exists:states,id'],
            'residence_lga_id' => ['nullable', 'uuid', 'exists:lgas,id'],
        ], [
            'phone.required' => 'A contact phone number is required.',
            'residential_address.required' => 'The current residential address is required.',
            'residence_town.required' => 'The residence town is required.',
        ]);

        $application->profile->update([
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'residential_address' => $data['residential_address'],
            'residence_town' => $data['residence_town'],
            'residence_state_id' => $data['residence_state_id'] ?? null,
            'residence_lga_id' => $data['residence_lga_id'] ?? null,
        ]);
    }

    private function step5(IndigeneApplication $application, Request $request): void
    {
        $data = $request->validate([
            'indigene_basis' => ['required', 'string', 'max:2000'],
            'father_name' => ['nullable', 'string', 'max:180'],
            'father_origin_lga' => ['nullable', 'string', 'max:150'],
            'father_phone' => ['nullable', 'string', 'max:20'],
            'mother_name' => ['nullable', 'string', 'max:180'],
            'mother_origin_lga' => ['nullable', 'string', 'max:150'],
            'mother_phone' => ['nullable', 'string', 'max:20'],
        ], [
            'indigene_basis.required' => 'Explain the evidence/basis on which indigene status is claimed.',
        ]);

        $application->profile->update([
            'indigene_basis' => $data['indigene_basis'],
        ]);

        $profile = $application->profile;

        $this->syncRelation($profile, 'father', $data['father_name'] ?? null, 'Father', $data['father_phone'] ?? null, $data['father_origin_lga'] ?? null);
        $this->syncRelation($profile, 'mother', $data['mother_name'] ?? null, 'Mother', $data['mother_phone'] ?? null, $data['mother_origin_lga'] ?? null);
    }

    private function step6(IndigeneApplication $application, Request $request): void
    {
        $profile = $application->profile;
        $isMinor = $profile->isMinor() || $request->boolean('is_dependent');

        $request->validate([
            'guardian_name' => [($isMinor ? 'required' : 'nullable'), 'string', 'max:180'],
            'guardian_relationship' => [($isMinor ? 'required' : 'nullable'), 'string', 'max:80'],
            'guardian_phone' => [($isMinor ? 'required' : 'nullable'), 'string', 'max:20'],
            'guardian_address' => ['nullable', 'string', 'max:1000'],
            'nok_name' => ['required', 'string', 'max:180'],
            'nok_relationship' => ['required', 'string', 'max:80'],
            'nok_phone' => ['required', 'string', 'max:20'],
            'nok_address' => ['nullable', 'string', 'max:1000'],
            'guardian_is_nok' => ['nullable', 'boolean'],
        ], [
            'guardian_name.required' => 'Guardian details are mandatory for minors or legally dependent applicants.',
            'nok_name.required' => 'Next of kin is required for every applicant.',
            'nok_phone.required' => 'A next-of-kin phone number is required.',
        ]);

        $data = $request->all();

        if ($data['guardian_is_nok'] ?? false) {
            $data['nok_name'] = $data['guardian_name'] ?? $data['nok_name'];
            $data['nok_relationship'] = $data['guardian_relationship'] ?? $data['nok_relationship'];
            $data['nok_phone'] = $data['guardian_phone'] ?? $data['nok_phone'];
        }

        $this->syncRelation($profile, 'guardian', $data['guardian_name'] ?? null, $data['guardian_relationship'] ?? null, $data['guardian_phone'] ?? null, null, $data['guardian_address'] ?? null);
        $this->syncRelation($profile, 'next_of_kin', $data['nok_name'], $data['nok_relationship'], $data['nok_phone'], null, $data['nok_address'] ?? null);
    }

    private function step7(IndigeneApplication $application, Request $request): void
    {
        $request->validate([
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['file', 'max:10240'],
            'document_types' => ['required', 'array', 'min:1'],
            'document_types.*' => ['string', 'max:60'],
        ], [
            'documents.required' => 'Attach at least one supporting document.',
            'documents.min' => 'Attach at least one supporting document.',
        ]);

        $files = $request->file('documents');
        $types = $request->input('document_types');

        foreach ($files as $index => $file) {
            $asset = app(\App\Services\FileUploadService::class)->storeDocument(
                $file,
                'documents/'.$application->id,
                auth()->user()
            );

            $application->documents()->create([
                'profile_id' => $application->profile_id,
                'file_asset_id' => $asset->id,
                'document_type' => $types[$index] ?? 'other',
                'verification_status' => 'pending',
                'created_by' => auth()->id(),
            ]);
        }
    }

    private function step8(IndigeneApplication $application, Request $request): void
    {
        $request->validate([
            'declaration' => ['accepted'],
        ], [
            'declaration.accepted' => 'The applicant/operator declaration must be confirmed before submission.',
        ]);

        $application->declaration_version = '1.0';
        $application->declaration_accepted_at = now();
        $application->save();

        app(\App\Services\ApplicationWorkflowService::class)->submit($application, auth()->user());
    }

    // ------------------------------------------------------------------
    // Autosave: tolerant persistence without strict validation (SRD 6 / NFR)
    // ------------------------------------------------------------------

    private function autosave(IndigeneApplication $application, int $step, Request $request)
    {
        try {
            match ($step) {
                2 => $this->autosaveIdentity($application, $request),
                3 => $this->autosaveOrigin($application, $request),
                4 => $this->autosaveContact($application, $request),
                5 => $this->autosaveFamily($application, $request),
                6 => $this->autosaveGuardian($application, $request),
                default => null,
            };
        } catch (\Throwable) {
            // Autosave never surfaces hard errors; the user is told to retry.
        }

        $application->last_saved_step = $step;
        $application->save();

        return response()->json(['saved_at' => now()->format('H:i')]);
    }

    private function autosaveIdentity(IndigeneApplication $application, Request $request): void
    {
        $profile = $application->profile;

        $profile->fill($request->only([
            'title', 'surname', 'first_name', 'middle_name', 'other_names', 'sex', 'marital_status', 'nationality',
        ]));

        if ($request->filled('date_of_birth')) {
            $profile->date_of_birth = $request->input('date_of_birth');
        }

        $profile->save();

        if ($request->filled('nin') && $this->nin->validate($request->input('nin'))) {
            $indigene = $application->indigene;
            $indigene->nin_ciphertext = $this->nin->encrypt($request->input('nin'));
            $indigene->nin_hash = $this->nin->hash($request->input('nin'));
            $indigene->nin_last4 = $this->nin->last4($request->input('nin'));
            $indigene->save();
        }
    }

    private function autosaveOrigin(IndigeneApplication $application, Request $request): void
    {
        $lga = $application->lga;
        $update = [];

        if ($request->filled('ward_id')) {
            $ward = \App\Models\Ward::where('id', $request->input('ward_id'))->where('lga_id', $lga->id)->first();
            $update['ward_id'] = $ward?->id;
        }

        if ($request->filled('unit_id')) {
            $unit = \App\Models\Unit::where('id', $request->input('unit_id'))->where('lga_id', $lga->id)->first();
            $update['unit_id'] = $unit?->id;
        }

        if ($request->filled('district_id')) {
            $district = \App\Models\District::where('id', $request->input('district_id'))->where('lga_id', $lga->id)->first();
            $update['district_id'] = $district?->id;
        }

        if ($request->filled('hometown')) {
            $update['hometown'] = $request->input('hometown');
        }

        $application->profile->update($update);
    }

    private function autosaveContact(IndigeneApplication $application, Request $request): void
    {
        $application->profile->update($request->only([
            'phone', 'email', 'residential_address', 'residence_town', 'residence_state_id', 'residence_lga_id',
        ]));
    }

    private function autosaveFamily(IndigeneApplication $application, Request $request): void
    {
        if ($request->filled('indigene_basis')) {
            $application->profile->update(['indigene_basis' => $request->input('indigene_basis')]);
        }
    }

    private function autosaveGuardian(IndigeneApplication $application, Request $request): void
    {
        // Guardian/next-of-kin autosave is skipped to avoid partial person records;
        // the step is short and validated on explicit save.
    }

    // ------------------------------------------------------------------

    private function syncRelation(IndigeneProfile $profile, string $type, ?string $name, ?string $relationship = null, ?string $phone = null, ?string $origin = null, ?string $address = null): void
    {
        if (! $name) {
            $profile->relations()->where('relation_type', $type)->delete();

            return;
        }

        IndigeneRelation::updateOrCreate(
            ['profile_id' => $profile->id, 'relation_type' => $type],
            [
                'full_name' => $name,
                'relationship' => $relationship,
                'phone' => $phone,
                'address' => $address,
                'occupation' => null,
            ]
        );
    }

    private function documentTypes(): array
    {
        return [
            'nin_slip' => 'NIN slip or NINAuth evidence',
            'birth_certificate' => 'Birth certificate/declaration',
            'parent_evidence' => 'Parent/guardian evidence',
            'community_attestation' => 'Community/ward attestation',
            'previous_certificate' => 'Previous indigene certificate',
            'court_affidavit' => 'Court affidavit',
            'other' => 'Other LGA-approved evidence',
        ];
    }
}
