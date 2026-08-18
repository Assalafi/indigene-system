<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lga;
use App\Models\LgaProfile;
use App\Models\OfficialSignatory;
use App\Services\AuditService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;

/**
 * SRD 27.4 - LGA profile and signatory management.
 */
class LgaProfileController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request)
    {
        $user = auth()->user();

        abort_unless($user->can('lga-profile.manage') || $user->can('lga-profile.view'), 403);

        $query = Lga::with(['state', 'profile', 'activeSignatory'])
            ->where('status', 'active')
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('id', $user->activeLga()?->id));

        if ($request->filled('q')) {
            $query->where('name', 'like', "%{$request->input('q')}%");
        }

        $lgas = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('admin.lga-profiles.index', compact('lgas'));
    }

    public function show(Lga $lga)
    {
        $user = auth()->user();

        abort_unless($user->isSystemAdmin() || $user->activeLga()?->id === $lga->id, 404);

        $lga->load(['state', 'profiles' => fn ($q) => $q->orderByDesc('version_no'), 'signatories' => fn ($q) => $q->orderByDesc('effective_from')]);

        return view('admin.lga-profiles.show', compact('lga'));
    }

    public function store(Lga $lga, Request $request, FileUploadService $uploads)
    {
        $user = auth()->user();

        abort_unless(
            $user->can('lga-profile.manage') && ($user->isSystemAdmin() || $user->activeLga()?->id === $lga->id),
            403
        );

        $data = $request->validate([
            'display_name' => ['nullable', 'string', 'max:180'],
            'office_address' => ['nullable', 'string', 'max:1000'],
            'support_phone' => ['nullable', 'string', 'max:20'],
            'support_email' => ['nullable', 'email', 'max:190'],
            'primary_colour' => ['nullable', 'string', 'max:20'],
            'secondary_colour' => ['nullable', 'string', 'max:20'],
            'certificate_heading' => ['nullable', 'string', 'max:1000'],
            'footer_text' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'coat_of_arms' => ['nullable', 'image', 'max:2048'],
        ]);

        $latest = LgaProfile::where('lga_id', $lga->id)->orderByDesc('version_no')->first();
        $versionNo = ($latest?->version_no ?? 0) + 1;

        // Publishing a new profile version must not alter historical certificate versions.
        $profile = LgaProfile::create([
            'lga_id' => $lga->id,
            'display_name' => $data['display_name'] ?? null,
            'office_address' => $data['office_address'] ?? null,
            'support_phone' => $data['support_phone'] ?? null,
            'support_email' => $data['support_email'] ?? null,
            'primary_colour' => $data['primary_colour'] ?? '#087A4B',
            'secondary_colour' => $data['secondary_colour'] ?? '#0B1F3A',
            'certificate_heading' => $data['certificate_heading'] ?? null,
            'footer_text' => $data['footer_text'] ?? null,
            'status' => 'published',
            'version_no' => $versionNo,
            'effective_from' => now()->toDateString(),
            'created_by' => auth()->id(),
            'approved_by' => auth()->id(),
        ]);

        if ($request->hasFile('logo')) {
            $asset = $uploads->storeImage($request->file('logo'), 'branding/'.$lga->id, auth()->user());
            $profile->logo_file_id = $asset->id;
            $profile->save();
        }

        if ($request->hasFile('coat_of_arms')) {
            $asset = $uploads->storeImage($request->file('coat_of_arms'), 'branding/'.$lga->id, auth()->user());
            $profile->coat_of_arms_file_id = $asset->id;
            $profile->save();
        }

        LgaProfile::where('lga_id', $lga->id)->where('id', '!=', $profile->id)->update(['status' => 'superseded']);

        $this->audit->record('lga_profile.published', LgaProfile::class, $profile->id, [], [
            'version_no' => $versionNo,
        ], 'medium');

        return redirect()->route('admin.lga-profiles.show', $lga)
            ->with('status', "LGA profile version {$versionNo} published. Historical certificate versions remain unchanged.");
    }

    public function storeSignatory(Lga $lga, Request $request, FileUploadService $uploads)
    {
        $user = auth()->user();

        abort_unless(
            $user->can('lga-profile.manage') && ($user->isSystemAdmin() || $user->activeLga()?->id === $lga->id),
            403
        );

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:180'],
            'office_title' => ['required', 'string', 'max:150'],
            'appointment_reference' => ['nullable', 'string', 'max:100'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'signature' => ['nullable', 'image', 'max:2048'],
            'seal' => ['nullable', 'image', 'max:2048'],
        ]);

        // Only one active primary signatory per LGA at a time.
        OfficialSignatory::where('lga_id', $lga->id)
            ->where('status', 'active')
            ->where('is_primary', true)
            ->update(['effective_to' => $data['effective_from']]);

        $signatory = OfficialSignatory::create([
            'lga_id' => $lga->id,
            'full_name' => $data['full_name'],
            'office_title' => $data['office_title'],
            'appointment_reference' => $data['appointment_reference'] ?? null,
            'effective_from' => $data['effective_from'],
            'effective_to' => $data['effective_to'] ?? null,
            'status' => 'active',
            'is_primary' => true,
            'created_by' => auth()->id(),
            'approved_by' => auth()->id(),
        ]);

        if ($request->hasFile('signature')) {
            $asset = $uploads->storeImage($request->file('signature'), 'signatories/'.$lga->id, auth()->user());
            $signatory->signature_file_id = $asset->id;
            $signatory->save();
        }

        if ($request->hasFile('seal')) {
            $asset = $uploads->storeImage($request->file('seal'), 'signatories/'.$lga->id, auth()->user());
            $signatory->seal_file_id = $asset->id;
            $signatory->save();
        }

        $this->audit->record('signatory.published', OfficialSignatory::class, $signatory->id, [], [
            'full_name' => $signatory->full_name,
        ], 'high');

        return redirect()->route('admin.lga-profiles.show', $lga)
            ->with('status', 'Signatory published. Historical certificate versions are unaffected.');
    }
}
