<?php

namespace App\Http\Controllers;

use App\Models\ReportExport;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function __construct(private ExportService $exports) {}

    public function myExports()
    {
        $exports = ReportExport::with('file')
            ->where('requested_by', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('reports.exports', compact('exports'));
    }

    public function create(Request $request)
    {
        abort_unless(auth()->user()->can('report.export'), 403);

        $data = $request->validate([
            'report_code' => ['required', 'string', 'max:60'],
            'format' => ['required', 'in:csv,xlsx,pdf'],
            'purpose' => ['required', 'string', 'max:2000'],
        ]);

        $user = auth()->user();
        $lgaScope = $user->isSystemAdmin() ? ($request->input('lga_scope_id') ?: null) : $user->activeLga()?->id;

        // Build the scoped filters from the current report page query.
        $filters = $request->except(['_token', 'report_code', 'format', 'purpose', 'lga_scope_id']);

        $export = $this->exports->request(
            $data['report_code'],
            $filters,
            $data['format'],
            $data['purpose'],
            $user,
            $lgaScope
        );

        $this->run($export);

        return redirect()->route('reports.exports')->with('status', 'Export queued and generated.');
    }

    /**
     * NFR-PERF-006: exports are processed out-of-band. In this deployment the
     * generation is synchronous but isolated; swap in a queued job for scale.
     */
    private function run(ReportExport $export): void
    {
        $user = auth()->user();
        $lgaId = $export->lga_scope_id;

        $rows = match ($export->report_code) {
            'registrations' => \App\Models\Indigene::query()
                ->with(['currentProfile', 'originLga'])
                ->when($lgaId, fn ($q) => $q->where('origin_lga_id', $lgaId))
                ->when($export->filters['from'] ?? null, fn ($q) => $q->whereBetween('created_at', [$export->filters['from'].' 00:00:00', $export->filters['to'].' 23:59:59']))
                ->limit(5000)
                ->get()
                ->map(fn ($i) => [
                    $i->registry_number,
                    $i->currentProfile?->displayName(),
                    $i->currentProfile?->sex,
                    $i->originLga?->name,
                    $i->lifecycle_status,
                    $i->created_at?->format('d/m/Y'),
                ]),
            'certificates' => \App\Models\Certificate::query()
                ->with(['indigene.currentProfile', 'lga'])
                ->when($lgaId, fn ($q) => $q->where('lga_id', $lgaId))
                ->limit(5000)
                ->get()
                ->map(fn ($c) => [
                    $c->certificate_number,
                    $c->indigene->currentProfile?->displayName(),
                    $c->lga?->name,
                    $c->status->label(),
                    $c->issued_at?->format('d/m/Y'),
                    $c->total_prints_cached,
                ]),
            default => collect(),
        };

        $this->exports->completeCsv(
            $export,
            $this->headersFor($export->report_code),
            $rows->getIterator()
        );
    }

    private function headersFor(string $code): array
    {
        return match ($code) {
            'registrations' => ['Registry number', 'Name', 'Sex', 'LGA', 'Status', 'Registered'],
            'certificates' => ['Certificate number', 'Holder', 'LGA', 'Status', 'Issued', 'Prints'],
            default => [],
        };
    }

    public function download(ReportExport $export)
    {
        abort_unless(
            $export->requested_by === auth()->id() || auth()->user()->isSystemAdmin(),
            403,
            'You can only download your own exports.'
        );

        $file = $export->file;

        if (! $file) {
            abort(404, 'This export is not ready.');
        }

        if ($export->expires_at && $export->expires_at->isPast()) {
            abort(410, 'This export has expired. Generate a new one.');
        }

        app(\App\Services\AuditService::class)->recordSensitiveAccess(
            ReportExport::class,
            $export->id,
            'report_export',
            'download',
            'Export download'
        );

        return Storage::disk($file->storage_disk)->download($file->object_key, $file->original_name);
    }
}
