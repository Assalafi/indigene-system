<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Lga;
use App\Models\State;
use App\Models\Unit;
use App\Models\Ward;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GeographyController extends Controller
{
    public function __construct(private AuditService $audit) {}

    private function assertGeographyView(): void
    {
        abort_unless(
            auth()->user()->can('geography.view')
            || auth()->user()->can('geography.manage-local')
            || auth()->user()->can('geography.manage-national'),
            403
        );
    }

    /**
     * AJAX dependency source for State -> LGA cascading selects (staff only).
     */
    public function lgasByState(Request $request)
    {
        abort_unless(auth()->user() && auth()->user()->isActive(), 403);

        $data = $request->validate([
            'state_id' => ['required', 'uuid', 'exists:states,id'],
        ]);

        return response()->json(
            Lga::where('state_id', $data['state_id'])
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    /**
     * AJAX dependency source for LGA -> Ward cascading selects (staff only).
     */
    public function wardsByLga(Request $request)
    {
        abort_unless(auth()->user() && auth()->user()->isActive(), 403);

        $data = $request->validate([
            'lga_id' => ['required', 'uuid', 'exists:lgas,id'],
        ]);

        return response()->json(
            Ward::where('lga_id', $data['lga_id'])
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    // ---------------------------------------------------------------- States & LGAs

    public function states(Request $request)
    {
        $this->assertGeographyView();

        $states = State::withCount('lgas')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', "%{$request->input('q')}%"))
            ->orderBy('name')
            ->paginate(25, ['*'], 'page_s')
            ->withQueryString();

        $lgas = Lga::with('state')
            ->when($request->filled('state_id'), fn ($q) => $q->where('state_id', $request->input('state_id')))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', "%{$request->input('q')}%"))
            ->orderBy('name')
            ->paginate(25, ['*'], 'page_l')
            ->withQueryString();

        $tab = $request->input('tab', 'states');

        return view('geography.states', compact('states', 'lgas', 'tab'));
    }

    public function showLga(Lga $lga)
    {
        $user = auth()->user();

        if (! $user->isSystemAdmin() && $user->activeLga()?->id !== $lga->id) {
            abort(404);
        }

        $lga->loadCount(['wards', 'units', 'districts']);
        $lga->load(['state', 'wards', 'districts']);

        return view('geography.lga-show', compact('lga'));
    }

    // ---------------------------------------------------------------- Wards & units

    public function wards(Request $request)
    {
        $this->assertGeographyView();

        $user = auth()->user();
        $lga = $user->activeLga();

        if (! $user->isSystemAdmin() && ! $lga) {
            abort(403, 'No active LGA assignment.');
        }

        $query = Ward::with(['lga.state', 'district', 'units' => fn ($q) => $q->where('status', 'active')])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('lga_id', $lga->id));

        if ($request->filled('lga_id') && $user->isSystemAdmin()) {
            $query->where('lga_id', $request->input('lga_id'));
        }

        if ($request->filled('q')) {
            $query->where('name', 'like', "%{$request->input('q')}%");
        }

        $wards = $query->orderBy('name')->paginate(25, ['*'], 'page_w')->withQueryString();

        $districts = District::with('lga')
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('lga_id', $lga->id))
            ->when($request->filled('lga_id') && $user->isSystemAdmin(), fn ($q) => $q->where('lga_id', $request->input('lga_id')))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', "%{$request->input('q')}%"))
            ->orderBy('name')
            ->paginate(15, ['*'], 'page_d')
            ->withQueryString();

        $units = Unit::with(['ward', 'lga'])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('lga_id', $lga->id))
            ->when($request->filled('lga_id') && $user->isSystemAdmin(), fn ($q) => $q->where('lga_id', $request->input('lga_id')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->orderBy('name')
            ->paginate(15, ['*'], 'page_u')
            ->withQueryString();

        $states = \App\Models\State::where('status', 'active')->orderBy('name')->get();
        $tab = $request->input('tab', 'districts');

        return view('geography.wards', compact('wards', 'districts', 'units', 'states', 'tab'));
    }

    public function storeLocalUnit(Request $request)
    {
        if (! auth()->user()->can('geography.manage-national') && ! auth()->user()->can('geography.manage-local')) {
            abort(403);
        }

        $user = auth()->user();
        $lga = $user->activeLga();

        if (! $user->isSystemAdmin()) {
            abort_if(! $lga, 403, 'No active LGA assignment.');

            if (! $user->can('geography.manage-local') || $request->input('lga_id') !== $lga->id) {
                abort(403, 'You can only manage geography within your assigned LGA.');
            }
        }

        $data = $request->validate([
            'lga_id' => ['required', 'uuid', 'exists:lgas,id'],
            'ward_id' => ['required', 'uuid', 'exists:wards,id'],
            'district_id' => ['nullable', 'uuid', 'exists:districts,id'],
            'names' => ['required', 'string', 'max:20000'],
            'codes' => ['nullable', 'string', 'max:20000'],
            'category' => ['required', 'in:village,community,village_unit,administrative_unit,polling_unit'],
            'source_name' => ['nullable', 'string', 'max:255'],
        ], [
            'ward_id.required' => 'Select the ward this unit belongs to.',
            'names.required' => 'Enter at least one village/community name.',
        ]);

        $ward = Ward::findOrFail($data['ward_id']);

        if ($ward->lga_id !== $data['lga_id']) {
            return back()->withErrors(['ward_id' => 'The ward does not belong to the selected LGA.']);
        }

        if (! empty($data['district_id'])) {
            $district = District::find($data['district_id']);

            if ($district->lga_id !== $data['lga_id']) {
                return back()->withErrors(['district_id' => 'The district does not belong to the selected LGA.']);
            }
        }

        $names = $this->parseLines($data['names'], 180);

        if (empty($names)) {
            throw ValidationException::withMessages(['names' => 'Enter at least one village/community name.']);
        }

        $codes = $this->parseLines($data['codes'] ?? '');
        $created = 0;

        foreach ($names as $index => $name) {
            $code = $codes[$index] ?? $this->generateCode($name, 'UNIT');

            $unit = Unit::create([
                'lga_id' => $data['lga_id'],
                'ward_id' => $ward->id,
                'district_id' => $data['district_id'] ?? null,
                'name' => $name,
                'category' => $data['category'],
                'code' => $code,
                'source_name' => $data['source_name'] ?? 'manual',
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);

            $this->audit->record('geography.unit_created', Unit::class, $unit->id, [], [
                'name' => $unit->name,
                'category' => $unit->category,
            ], 'medium');

            $created++;
        }

        return back()->with('status', $created.' village/community unit(s) added to '.$ward->name.' Ward.');
    }

    public function storeWard(Request $request)
    {
        $user = auth()->user();
        $lga = $user->activeLga();

        if (! $user->isSystemAdmin()) {
            abort_if(! $lga, 403, 'No active LGA assignment.');

            if (! $user->can('geography.manage-local') || $request->input('lga_id') !== $lga->id) {
                abort(403, 'You can only manage geography within your assigned LGA.');
            }
        }

        $data = $request->validate([
            'lga_id' => ['required', 'uuid', 'exists:lgas,id'],
            'district_id' => ['nullable', 'uuid', 'exists:districts,id'],
            'names' => ['required', 'string', 'max:20000'],
            'codes' => ['nullable', 'string', 'max:20000'],
        ], [
            'names.required' => 'Enter at least one ward name.',
        ]);

        $names = $this->parseLines($data['names'], 150);

        if (empty($names)) {
            throw ValidationException::withMessages(['names' => 'Enter at least one ward name.']);
        }

        $codes = $this->parseLines($data['codes'] ?? '');
        $created = 0;

        foreach ($names as $index => $name) {
            $code = $codes[$index] ?? $this->generateCode($name, 'WARD');

            $ward = Ward::create([
                'lga_id' => $data['lga_id'],
                'district_id' => $data['district_id'] ?? null,
                'name' => $name,
                'code' => $code,
                'source_name' => 'manual',
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);
            $this->audit->record('geography.ward_created', Ward::class, $ward->id, [], [
                'name' => $ward->name,
            ], 'medium');

            $created++;
        }

        return back()->with('status', $created.' ward(s) added.');
    }

    public function storeDistrict(Request $request)
    {
        $user = auth()->user();
        $lga = $user->activeLga();

        if (! $user->isSystemAdmin()) {
            abort_if(! $lga, 403, 'No active LGA assignment.');

            if (! $user->can('geography.manage-local') || $request->input('lga_id') !== $lga->id) {
                abort(403, 'You can only manage geography within your assigned LGA.');
            }
        }

        $data = $request->validate([
            'lga_id' => ['required', 'uuid', 'exists:lgas,id'],
            'names' => ['required', 'string', 'max:20000'],
            'codes' => ['nullable', 'string', 'max:20000'],
        ], [
            'names.required' => 'Enter at least one district name.',
        ]);

        $names = $this->parseLines($data['names'], 150);

        if (empty($names)) {
            throw ValidationException::withMessages(['names' => 'Enter at least one district name.']);
        }

        $codes = $this->parseLines($data['codes'] ?? '');
        $created = 0;

        foreach ($names as $index => $name) {
            $code = $codes[$index] ?? $this->generateCode($name, 'DST');

            $district = District::create([
                'lga_id' => $data['lga_id'],
                'name' => $name,
                'code' => $code,
                'source_name' => 'manual',
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);

            $this->audit->record('geography.district_created', District::class, $district->id, [], [
                'name' => $district->name,
            ], 'medium');

            $created++;
        }

        return back()->with('status', $created.' district(s) added.');
    }

    private function parseLines(?string $value, int $maxLength = 500): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $lines = [];

        foreach (preg_split('/\r\n|\r|\n/', $value) as $line) {
            $line = trim($line);

            if ($line !== '') {
                $lines[] = mb_substr($line, 0, $maxLength);
            }
        }

        return array_slice($lines, 0, 100);
    }

    private function generateCode(string $name, string $prefix): string
    {
        $slug = Str::slug($name);

        return strtoupper((substr($slug, 0, 10) !== '' ? substr($slug, 0, 10) : $prefix).'-'.Str::random(4));
    }

    public function update(Request $request)
    {
        $this->assertGeographyManage();

        $data = $request->validate([
            'type' => ['required', 'in:district,ward,unit'],
            'id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:180'],
            'code' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'in:village,community,village_unit,administrative_unit,polling_unit'],
            'status' => ['nullable', 'in:active,retired'],
        ]);

        $model = $this->findGeography($data['type'], $data['id']);
        $this->assertGeographyScope($model);

        $model->name = $data['name'];

        if (! empty($data['code'])) {
            $model->code = $data['code'];
        }

        if ($data['type'] === 'unit' && ! empty($data['category'])) {
            $model->category = $data['category'];
        }

        if (! empty($data['status'])) {
            $model->status = $data['status'];
        }

        $model->updated_by = auth()->id();
        $model->save();

        $this->audit->record('geography.updated', get_class($model), $model->id, [], [
            'name' => $model->name,
            'status' => $model->status,
        ], 'medium');

        return back()->with('status', 'Geography record updated.');
    }

    public function destroy(Request $request)
    {
        $this->assertGeographyManage();

        $data = $request->validate([
            'type' => ['required', 'in:district,ward,unit'],
            'id' => ['required', 'uuid'],
        ]);

        $model = $this->findGeography($data['type'], $data['id']);
        $this->assertGeographyScope($model);

        // SRD FR-GEO-007: referenced geography is never hard-deleted; retire it instead.
        if ($this->isReferenced($model, $data['type'])) {
            $model->status = 'retired';
            $model->effective_to = now()->toDateString();
            $model->updated_by = auth()->id();
            $model->save();

            $this->audit->record('geography.retired', get_class($model), $model->id, [
                'status' => 'active',
            ], [
                'status' => 'retired',
            ], 'high');

            return back()->with('info', 'This record is referenced by existing applications or certificates, so it was retired instead of deleted. Historical records remain readable.');
        }

        $model->delete();

        $this->audit->record('geography.deleted', get_class($model), $model->id, [], [], 'high');

        return back()->with('status', 'Geography record deleted.');
    }

    private function assertGeographyManage(): void
    {
        abort_unless(
            auth()->user()->can('geography.manage-national') || auth()->user()->can('geography.manage-local'),
            403,
            'You are not authorised to manage geography.'
        );
    }

    private function findGeography(string $type, string $id): District|Ward|Unit
    {
        return match ($type) {
            'district' => District::findOrFail($id),
            'ward' => Ward::findOrFail($id),
            'unit' => Unit::findOrFail($id),
        };
    }

    private function assertGeographyScope(District|Ward|Unit $model): void
    {
        $user = auth()->user();

        if ($user->isSystemAdmin()) {
            return;
        }

        $lga = $user->activeLga();

        abort_if(! $lga, 403, 'No active LGA assignment.');
        abort_if($model->lga_id !== $lga->id, 403, 'You can only manage geography within your assigned LGA.');
    }

    private function isReferenced(District|Ward|Unit $model, string $type): bool
    {
        return match ($type) {
            'district' => \App\Models\Ward::where('district_id', $model->id)->exists()
                || \App\Models\Unit::where('district_id', $model->id)->exists()
                || \App\Models\IndigeneProfile::where('district_id', $model->id)->exists(),
            'ward' => \App\Models\Unit::where('ward_id', $model->id)->exists()
                || \App\Models\IndigeneProfile::where('ward_id', $model->id)->exists(),
            'unit' => \App\Models\IndigeneProfile::where('unit_id', $model->id)->exists(),
        };
    }

    public function retire(Request $request)
    {
        if (! auth()->user()->can('geography.manage-national')) {
            abort(403);
        }
        $data = $request->validate([
            'type' => ['required', 'in:district,ward,unit'],
            'id' => ['required', 'uuid'],
            'merge_into_id' => ['nullable', 'uuid'],
        ]);

        $model = match ($data['type']) {
            'district' => District::findOrFail($data['id']),
            'ward' => Ward::findOrFail($data['id']),
            'unit' => Unit::findOrFail($data['id']),
        };

        // Never hard-delete referenced geography (SRD FR-GEO-007).
        $model->status = 'retired';
        $model->effective_to = now()->toDateString();

        if ($data['type'] === 'ward' && $data['merge_into_id']) {
            $model->merged_into_ward_id = $data['merge_into_id'];
        }

        if ($data['type'] === 'unit' && $data['merge_into_id']) {
            $model->merged_into_unit_id = $data['merge_into_id'];
        }

        $model->updated_by = auth()->id();
        $model->save();

        $this->audit->record('geography.retired', get_class($model), $model->id, [
            'status' => 'active',
        ], [
            'status' => 'retired',
        ], 'medium');

        return back()->with('status', 'Record retired. Historical records remain readable.');
    }
}


