<?php

namespace App\Http\Controllers;

use App\Models\ConsentRecord;
use App\Models\District;
use App\Models\Indigene;
use App\Models\IndigeneApplication;
use App\Models\IndigeneProfile;
use App\Models\IndigeneRelation;
use App\Models\Lga;
use App\Models\State;
use App\Models\Unit;
use App\Models\Ward;
use App\Services\ApplicationWorkflowService;
use App\Services\DuplicateDetectionService;
use App\Services\FileUploadService;
use App\Services\IndigeneProfileVersionService;
use App\Services\NinProtectionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * One-screen onboarding, no draft step. The application is created and
 * submitted for LGA approval in a single action.
 */
class ApplicationWizardController extends Controller
{
    public function __construct(
        private NinProtectionService $nin,
        private IndigeneProfileVersionService $profiles,
        private DuplicateDetectionService $duplicates,
        private FileUploadService $uploads,
    ) {}

    public function create(Request $request)
    {
        $this->authorize('create', IndigeneApplication::class);

        $lga = auth()->user()->activeLga();

        if (! $lga && auth()->user()->isSystemAdmin()) {
            if ($request->filled('lga_id')) {
                $lga = Lga::where('status', 'active')->find($request->input('lga_id'));

                if (! $lga) {
                    abort(404);
                }
            } else {
                return view('applications.select-lga', [
                    'states' => State::where('status', 'active')->orderBy('name')->get(),
                    'lgas' => Lga::with('state')->where('status', 'active')->orderBy('name')->get(),
                ]);
            }
        }

        $state = $lga?->state;

        if (! $lga || ! $state) {
            abort(403, 'You need an active LGA assignment to register indigenes.');
        }

        $units = Unit::where('lga_id', $lga->id)->where('status', 'active')->where('category', '!=', 'polling_unit')->orderBy('name')->get();

        return view('applications.create', [
            'application' => null,
            'profile' => null,
            'indigene' => null,
            'lga' => $lga,
            'state' => $state,
            'districts' => District::where('lga_id', $lga->id)->where('status', 'active')->orderBy('name')->get(),
            'wards' => Ward::where('lga_id', $lga->id)->where('status', 'active')->orderBy('name')->get(),
            'units' => $units,
            'unitOptions' => $units->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'ward_id' => $u->ward_id, 'district_id' => $u->district_id])->values(),
            'guardian' => null,
            'creating' => true,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', IndigeneApplication::class);

        $lga = auth()->user()->activeLga();

        if (! $lga && auth()->user()->isSystemAdmin()) {
            $lga = Lga::where('status', 'active')->find($request->input('lga_id'));
        }

        $state = $lga?->state;

        if (! $lga || ! $state) {
            abort(403, 'You need an active LGA assignment to register indigenes.');
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($lga, $state, $request) {
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
                'nationality' => 'Nigerian',
                'origin_state_id' => $state->id,
                'origin_lga_id' => $lga->id,
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

            return $this->populateAndSubmit($application, $request);
        });
    }

    public function edit(IndigeneApplication $application)
    {
        $this->authorize('update', $application);

        $application->load('profile', 'indigene', 'lga.state');

        $lga = $application->lga;
        $state = $lga->state;

        $units = Unit::where('lga_id', $lga->id)->where('status', 'active')->where('category', '!=', 'polling_unit')->orderBy('name')->get();

        return view('applications.create', [
            'application' => $application,
            'profile' => $application->profile,
            'indigene' => $application->indigene,
            'lga' => $lga,
            'state' => $state,
            'districts' => District::where('lga_id', $lga->id)->where('status', 'active')->orderBy('name')->get(),
            'wards' => Ward::where('lga_id', $lga->id)->where('status', 'active')->orderBy('name')->get(),
            'units' => $units,
            'unitOptions' => $units->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'ward_id' => $u->ward_id, 'district_id' => $u->district_id])->values(),
            'guardian' => $application->profile->relations()->where('relation_type', 'guardian')->first(),
            'creating' => false,
        ]);
    }

    public function saveAndSubmit(IndigeneApplication $application, Request $request)
    {
        $this->authorize('update', $application);

        return $this->populateAndSubmit($application, $request);
    }

    private function populateAndSubmit(IndigeneApplication $application, Request $request)
    {
        $lga = $application->lga;

        $data = $request->validate([
            'acknowledge' => ['accepted'],
            'nin' => ['nullable', 'string', 'digits:11'],
            'surname' => ['required', 'string', 'min:2', 'max:100'],
            'first_name' => ['required', 'string', 'min:2', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', 'in:male,female'],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'nationality' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:190'],
            'district_id' => ['required', 'uuid', 'exists:districts,id'],
            'ward_id' => ['nullable', 'uuid', 'exists:wards,id'],
            'unit_id' => ['required', 'uuid', 'exists:units,id'],
            'guardian_name' => ['required', 'string', 'max:180'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ], [
            'acknowledge.accepted' => 'You must confirm the declaration before submitting.',
            'nin.digits' => 'The NIN must be exactly 11 digits when provided.',
            'guardian_name.required' => 'A guardian or parent is required.',
        ]);

        $districtRecord = District::findOrFail($data['district_id']);

        if ($districtRecord->lga_id !== $lga->id) {
            throw ValidationException::withMessages(['district_id' => 'The selected district does not belong to your LGA.']);
        }

        $ward = null;

        if (! empty($data['ward_id'])) {
            $ward = Ward::findOrFail($data['ward_id']);

            if ($ward->lga_id !== $lga->id) {
                throw ValidationException::withMessages(['ward_id' => 'The selected ward does not belong to your LGA.']);
            }
        }

        $unit = Unit::findOrFail($data['unit_id']);

        // No district/ward/village hierarchy mapping is enforced; the village only
        // needs to belong to the LGA (its ward is auto-filled on selection).
        if ($unit->lga_id !== $lga->id) {
            throw ValidationException::withMessages(['unit_id' => 'The selected unit does not belong to your LGA.']);
        }

        if ($unit->isPollingUnit()) {
            throw ValidationException::withMessages(['unit_id' => 'Polling units cannot be used as a village of origin.']);
        }

        $indigene = $application->indigene;

        if ($request->filled('nin')) {
            try {
                $indigene->nin_ciphertext = $this->nin->encrypt($data['nin']);
                $indigene->nin_hash = $this->nin->hash($data['nin']);
                $indigene->nin_last4 = $this->nin->last4($data['nin']);
                $indigene->nin_verification_status = 'unverified';
                $indigene->save();
            } catch (\Illuminate\Database\UniqueConstraintViolationException) {
                throw ValidationException::withMessages([
                    'nin' => 'This NIN is already registered to another record in the system.',
                ]);
            }
        }

        $profile = $application->profile;

        $profile->fill([
            'surname' => $data['surname'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'date_of_birth' => $data['date_of_birth'],
            'sex' => $data['sex'],
            'marital_status' => $data['marital_status'] ?? null,
            'nationality' => $data['nationality'] ?? 'Nigerian',
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'district_id' => $districtRecord->id,
            'ward_id' => $ward?->id,
            'unit_id' => $unit->id,
        ]);

        if ($request->hasFile('photo')) {
            $file = $this->uploads->storeImage($request->file('photo'), 'photos', auth()->user());
            $profile->photo_file_id = $file->id;
        }

        $profile->save();

        IndigeneRelation::updateOrCreate(
            ['profile_id' => $profile->id, 'relation_type' => 'guardian'],
            [
                'full_name' => $data['guardian_name'],
                'phone' => $data['guardian_phone'] ?? null,
                'is_primary' => true,
            ]
        );

        $application->declaration_version = '1.0';
        $application->declaration_accepted_at = now();
        $application->save();

        ConsentRecord::updateOrCreate(
            ['indigene_id' => $indigene->id, 'application_id' => $application->id],
            [
                'data_subject_type' => 'applicant',
                'notice_version' => '1.0',
                'lawful_basis' => 'public_interest_task',
                'purpose_codes' => ['indigene_register', 'lga_approval', 'certificate_issuance'],
                'consent_required' => false,
                'accepted' => true,
                'captured_method' => 'portal',
                'captured_by' => auth()->id(),
                'captured_at' => now(),
            ]
        );

        $this->duplicates->detect($indigene, $profile, $application);

        $workflow = app(ApplicationWorkflowService::class);
        $wasApproved = $application->status === \App\Enums\ApplicationStatus::Approved;

        $workflow->submit($application, auth()->user());

        return redirect()->route('applications.show', $application)
            ->with('status', $wasApproved
                ? 'Application updated. The certificate has been re-issued with the corrected details.'
                : 'Application '.$application->application_number.' has been submitted for approval.');
    }

    /**
     * Backward compatibility: old wizard URLs now open the single form.
     */
    public function show(IndigeneApplication $application, int $step)
    {
        return $this->edit($application);
    }

    public function storeLegacy(IndigeneApplication $application, int $step, Request $request)
    {
        return $this->saveAndSubmit($application, $request);
    }
}
