<?php

namespace App\Http\Controllers;

use App\Models\GeographyImportBatch;
use App\Models\Lga;
use App\Models\State;
use App\Models\Ward;
use App\Services\AuditService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;

/**
 * SRD 24.3 - four-stage geography import: upload, map, dry-run, publish.
 */
class GeographyImportController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index()
    {
        if (! app(\App\Policies\GeographyPolicy::class)->import(auth()->user())) {
            abort(403);
        }

        $batches = GeographyImportBatch::with(['importer', 'publisher'])->orderByDesc('created_at')->paginate(25);

        return view('geography.imports-index', compact('batches'));
    }

    public function create()
    {
        if (! app(\App\Policies\GeographyPolicy::class)->import(auth()->user())) {
            abort(403);
        }

        return view('geography.import-create');
    }

    public function store(Request $request, FileUploadService $uploads)
    {
        if (! app(\App\Policies\GeographyPolicy::class)->import(auth()->user())) {
            abort(403);
        }

        $data = $request->validate([
            'dataset_type' => ['required', 'in:states,lgas,wards,units'],
            'source_name' => ['required', 'string', 'max:255'],
            'source_reference' => ['nullable', 'string', 'max:255'],
            'dataset_version' => ['nullable', 'string', 'max:50'],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
        ]);

        $asset = $uploads->storeDocument($request->file('file'), 'imports', auth()->user());

        $batch = GeographyImportBatch::create([
            'source_name' => $data['source_name'],
            'source_reference' => $data['source_reference'] ?? null,
            'dataset_type' => $data['dataset_type'],
            'dataset_version' => $data['dataset_version'] ?? null,
            'file_asset_id' => $asset->id,
            'checksum_sha256' => $asset->sha256,
            'status' => 'uploaded',
            'imported_by' => auth()->id(),
        ]);

        $report = $this->dryRun($batch);

        $batch->update([
            'status' => 'validated',
            'row_count' => $report['row_count'],
            'error_count' => $report['errors_count'],
            'validation_report' => $report,
        ]);

        $this->audit->record('geography.import_uploaded', GeographyImportBatch::class, $batch->id, [], [
            'dataset_type' => $batch->dataset_type,
            'source' => $batch->source_name,
        ], 'medium');

        return redirect()->route('geography.imports.show', $batch)
            ->with('status', 'File uploaded and validated. Review the dry-run report before publishing.');
    }

    public function show(GeographyImportBatch $batch)
    {
        if (! app(\App\Policies\GeographyPolicy::class)->import(auth()->user())) {
            abort(403);
        }

        return view('geography.import-show', compact('batch'));
    }

    public function publish(GeographyImportBatch $batch)
    {
        if (! app(\App\Policies\GeographyPolicy::class)->import(auth()->user())) {
            abort(403);
        }

        if ($batch->status === 'published') {
            return back()->with('info', 'This batch has already been published.');
        }

        $report = $batch->validation_report;

        if (! $report || ($report['errors_count'] ?? 0) > 0) {
            return back()->withErrors(['batch' => 'Resolve all validation errors before publishing.']);
        }

        $rows = $report['rows'];
        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            try {
                $this->applyRow($batch, $row);
                $inserted++;
            } catch (\Throwable) {
                $skipped++;
            }
        }

        $batch->update([
            'status' => 'published',
            'inserted_count' => $inserted,
            'updated_count' => $updated,
            'skipped_count' => $skipped,
            'published_by' => auth()->id(),
            'published_at' => now(),
        ]);

        $this->audit->record('geography.import_published', GeographyImportBatch::class, $batch->id, [], [
            'inserted' => $inserted,
            'skipped' => $skipped,
        ], 'high');

        return redirect()->route('geography.imports.index')
            ->with('status', "Batch published: {$inserted} rows applied, {$skipped} skipped.");
    }

    private function dryRun(GeographyImportBatch $batch): array
    {
        $content = \Illuminate\Support\Facades\Storage::disk($batch->fileAsset->storage_disk)->get($batch->fileAsset->object_key);
        $lines = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $content)));
        $errors = [];
        $rows = [];

        foreach ($lines as $index => $line) {
            if ($index === 0) {
                continue; // header row
            }

            $columns = str_getcsv($line);
            $row = $this->normalizeRow($batch->dataset_type, $columns);

            if (is_string($row)) {
                $errors[] = "Line ".($index + 1).': '.$row;
                continue;
            }

            $rows[] = $row;
        }

        return [
            'row_count' => count($rows),
            'errors_count' => count($errors),
            'errors' => $errors,
            'rows' => $rows,
        ];
    }

    private function normalizeRow(string $type, array $columns): array|string
    {
        return match ($type) {
            'states' => $this->normalizeState($columns),
            'lgas' => $this->normalizeLga($columns),
            'wards' => $this->normalizeWard($columns),
            'units' => $this->normalizeUnit($columns),
            default => 'Unknown dataset type.',
        };
    }

    private function normalizeState(array $c): array|string
    {
        if (count($c) < 2 || $c[0] === '' || $c[1] === '') {
            return 'Missing code or name.';
        }

        return ['code' => trim($c[0]), 'name' => trim($c[1]), 'capital' => trim($c[2] ?? ''), 'type' => strtolower(trim($c[3] ?? '')) === 'fct' ? 'fct' : 'state'];
    }

    private function normalizeLga(array $c): array|string
    {
        if (count($c) < 3 || $c[0] === '' || $c[1] === '' || $c[2] === '') {
            return 'Missing state code, LGA code or name.';
        }

        return ['state_code' => trim($c[0]), 'code' => trim($c[1]), 'name' => trim($c[2]), 'headquarters' => trim($c[3] ?? '')];
    }

    private function normalizeWard(array $c): array|string
    {
        if (count($c) < 3 || $c[0] === '' || $c[1] === '' || $c[2] === '') {
            return 'Missing LGA code, ward code or name.';
        }

        return ['lga_code' => trim($c[0]), 'code' => trim($c[1]), 'name' => trim($c[2])];
    }

    private function normalizeUnit(array $c): array|string
    {
        if (count($c) < 4 || $c[0] === '' || $c[1] === '' || $c[2] === '' || $c[3] === '') {
            return 'Missing LGA code, ward code, unit code or name.';
        }

        return ['lga_code' => trim($c[0]), 'ward_code' => trim($c[1]), 'code' => trim($c[2]), 'name' => trim($c[3]), 'category' => trim($c[4] ?? 'village')];
    }

    private function applyRow(GeographyImportBatch $batch, array $row): void
    {
        match ($batch->dataset_type) {
            'states' => State::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'type' => $row['type'],
                    'capital' => $row['capital'] ?: null,
                    'status' => 'active',
                    'source_name' => $batch->source_name,
                    'source_reference' => $batch->source_reference,
                    'created_by' => auth()->id(),
                ]
            ),
            'lgas' => $this->applyLga($batch, $row),
            'wards' => $this->applyWard($batch, $row),
            'units' => $this->applyUnit($batch, $row),
            default => throw new \RuntimeException('Unknown dataset type'),
        };
    }

    private function applyLga(GeographyImportBatch $batch, array $row): void
    {
        $state = State::where('code', $row['state_code'])->firstOrFail();

        Lga::updateOrCreate(
            ['state_id' => $state->id, 'code' => $row['code']],
            [
                'name' => $row['name'],
                'headquarters' => $row['headquarters'] ?: null,
                'status' => 'active',
                'source_name' => $batch->source_name,
                'source_reference' => $batch->source_reference,
                'created_by' => auth()->id(),
            ]
        );
    }

    private function applyWard(GeographyImportBatch $batch, array $row): void
    {
        $lga = Lga::where('code', $row['lga_code'])->firstOrFail();

        Ward::updateOrCreate(
            ['lga_id' => $lga->id, 'code' => $row['code']],
            [
                'name' => $row['name'],
                'status' => 'active',
                'source_name' => $batch->source_name,
                'source_reference' => $batch->source_reference,
                'import_batch_id' => $batch->id,
                'created_by' => auth()->id(),
            ]
        );
    }

    private function applyUnit(GeographyImportBatch $batch, array $row): void
    {
        $lga = Lga::where('code', $row['lga_code'])->firstOrFail();
        $ward = Ward::where('lga_id', $lga->id)->where('code', $row['ward_code'])->firstOrFail();
        $category = in_array($row['category'], ['village', 'community', 'village_unit', 'administrative_unit', 'polling_unit'], true)
            ? $row['category']
            : 'village';

        \App\Models\Unit::updateOrCreate(
            ['ward_id' => $ward->id, 'category' => $category, 'code' => $row['code']],
            [
                'lga_id' => $lga->id,
                'name' => $row['name'],
                'status' => 'active',
                'source_name' => $batch->source_name,
                'source_reference' => $batch->source_reference,
                'import_batch_id' => $batch->id,
                'created_by' => auth()->id(),
            ]
        );
    }
}
