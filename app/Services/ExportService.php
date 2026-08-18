<?php

namespace App\Services;

use App\Models\FileAsset;
use App\Models\ReportExport;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * SRD 16.8 / 62 - authorised, masked and queued exports with spreadsheet safety.
 */
class ExportService
{
    public function __construct(private AuditService $audit) {}

    public function request(string $reportCode, array $filters, string $format, string $purpose, User $user, ?string $lgaScopeId = null): ReportExport
    {
        if (! in_array($format, ['csv', 'xlsx', 'pdf'], true)) {
            throw new HttpException(422, 'Unsupported export format.');
        }

        if (trim($purpose) === '') {
            throw new HttpException(422, 'A purpose is required for every export.');
        }

        $export = ReportExport::create([
            'report_code' => $reportCode,
            'requested_by' => $user->id,
            'lga_scope_id' => $lgaScopeId,
            'filters' => $filters,
            'format' => $format,
            'purpose' => $purpose,
            'status' => 'queued',
            'expires_at' => now()->addDay(),
        ]);

        $this->audit->record('export.requested', ReportExport::class, $export->id, [], [
            'report_code' => $reportCode,
            'format' => $format,
            'lga_scope_id' => $lgaScopeId,
        ], 'medium', $user);

        return $export;
    }

    public function completeCsv(ReportExport $export, array $headers, iterable $rows): FileAsset
    {
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            fputcsv($handle, array_map(fn ($cell) => $this->sanitizeCell((string) $cell), $row));
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $this->store($export, $content, 'csv', 'text/csv');
    }

    public function completePdf(ReportExport $export, string $html): FileAsset
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'landscape')->output();

        return $this->store($export, $pdf, 'pdf', 'application/pdf');
    }

    /**
     * FR-REP-006 - neutralise formula injection in CSV/XLSX exports.
     */
    public function sanitizeCell(string $cell): string
    {
        if ($cell !== '' && in_array($cell[0], ['=', '+', '-', '@'], true)) {
            return "'".$cell;
        }

        return $cell;
    }

    private function store(ReportExport $export, string $content, string $extension, string $mime): FileAsset
    {
        $sha = hash('sha256', $content);
        $key = 'exports/'.$export->id.'/'.substr($sha, 0, 16).'.'.$extension;

        Storage::disk('private')->put($key, $content);

        $file = FileAsset::create([
            'storage_disk' => 'private',
            'object_key' => $key,
            'original_name' => str_replace('-', '_', $export->report_code).'.'.$extension,
            'mime_type' => $mime,
            'extension' => $extension,
            'size_bytes' => strlen($content),
            'sha256' => $sha,
            'malware_scan_status' => 'not_required',
            'malware_scanned_at' => now(),
            'uploaded_by' => $export->requested_by,
            'status' => 'available',
        ]);

        $export->status = 'completed';
        $export->file_id = $file->id;
        $export->completed_at = now();
        $export->save();

        $this->audit->record('export.completed', ReportExport::class, $export->id, [], [
            'file_id' => $file->id,
            'size_bytes' => $file->size_bytes,
        ], 'medium');

        return $file;
    }
}
