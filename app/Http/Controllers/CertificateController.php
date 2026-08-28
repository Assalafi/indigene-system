<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\CertificatePrintEvent;
use App\Services\CertificateRenderService;
use App\Services\CertificateStatusService;
use App\Services\PrintEventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Certificate::class);

        $user = auth()->user();
        $lga = $user->activeLga();

        $query = Certificate::with(['indigene.currentProfile', 'lga', 'approver'])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('lga_id', $lga->id));

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($q) use ($term) {
                $q->where('certificate_number', 'like', "%{$term}%")
                    ->orWhereHas('indigene', fn ($i) => $i
                        ->where('registry_number', 'like', "%{$term}%")
                        ->orWhereHas('currentProfile', fn ($p) => $p
                            ->where('surname', 'like', "%{$term}%")
                            ->orWhere('first_name', 'like', "%{$term}%")));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('issued_at', [$request->input('from').' 00:00:00', $request->input('to').' 23:59:59']);
        }

        if ($request->filled('print_min')) {
            $query->where('total_prints_cached', '>=', (int) $request->input('print_min'));
        }

        $certificates = $query->orderByDesc('issued_at')->paginate(25)->withQueryString();

        return view('certificates.index', compact('certificates'));
    }

    public function show(Certificate $certificate)
    {
        $this->authorize('view', $certificate);

        $certificate->load([
            'indigene.currentProfile.ward',
            'indigene.currentProfile.unit',
            'indigene.currentProfile.district',
            'indigene.currentProfile.photoFile',
            'lga.state',
            'lga.activeSignatory',
            'versions.pdfFile',
            'versions.signatory',
            'printEvents.requester',
            'statusEvents.actor',
            'application',
        ]);

        $canPrint = auth()->user()->can('certificate.print-action', $certificate);
        $canManageStatus = auth()->user()->can('changeStatus', $certificate);

        return view('certificates.show', compact('certificate', 'canPrint', 'canManageStatus'));
    }

    public function issue(Certificate $certificate, CertificateRenderService $renderer)
    {
        $this->authorize('issue', $certificate);

        $version = $renderer->issue($certificate, auth()->user());

        return redirect()->route('certificates.show', $certificate)
            ->with('status', 'Certificate '.$certificate->certificate_number.' issued successfully.');
    }

    public function printHistory(Request $request)
    {
        $this->authorize('viewPrintHistory', Certificate::class);

        $user = auth()->user();
        $lga = $user->activeLga();

        $query = CertificatePrintEvent::with(['certificate.indigene.currentProfile', 'requester'])
            ->when(! $user->isSystemAdmin(), fn ($q) => $q->where('requester_lga_id', $lga->id));

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->whereHas('certificate', fn ($c) => $c
                ->where('certificate_number', 'like', "%{$term}%")
                ->orWhereHas('indigene.currentProfile', fn ($p) => $p
                    ->where('surname', 'like', "%{$term}%")
                    ->orWhere('first_name', 'like', "%{$term}%")));
        }

        if ($request->filled('copy_type')) {
            $query->where('copy_type', $request->input('copy_type'));
        }

        $events = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('certificates.print-history', compact('events'));
    }

    public function createPrint(Certificate $certificate, Request $request, PrintEventService $prints): \Symfony\Component\HttpFoundation\Response
    {
        if (! auth()->user()->can('certificate.print-action', $certificate)) {
            abort(403, 'This certificate is not eligible for printing.');
        }

        $data = $request->validate([
            'reason_code' => ['nullable', 'string', 'max:60'],
            'reason_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $idempotencyKey = $request->input('idempotency_key') ?? (string) str()->uuid();
        $event = $prints->createPrintEvent(
            $certificate,
            auth()->user(),
            $idempotencyKey,
            $data['reason_code'] ?? ($certificate->total_prints_cached > 0 ? 'reprint' : 'initial_issue'),
            $data['reason_note'] ?? null
        );

        // Serve the watermarked copy directly in the browser (inline, not a download).
        $pdf = $event->pdfFile ?? $event->version?->pdfFile;

        if (! $pdf || ! Storage::disk($pdf->storage_disk)->exists($pdf->object_key)) {
            abort(404, 'The PDF for this print copy is not available.');
        }

        app(\App\Services\AuditService::class)->recordSensitiveAccess(
            Certificate::class,
            $certificate->id,
            'certificate_pdf',
            'download',
            'Authorised print copy download',
        );

        return response(Storage::disk($pdf->storage_disk)->get($pdf->object_key))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$certificate->certificate_number.'-copy-'.str_pad((string) $event->print_number, 2, '0', STR_PAD_LEFT).'.pdf"');
    }

    public function printResult(Certificate $certificate, CertificatePrintEvent $event)
    {
        $this->authorize('view', $certificate);

        return view('certificates.print-result', compact('certificate', 'event'));
    }

    public function download(Certificate $certificate, CertificatePrintEvent $event): StreamedResponse
    {
        $this->authorize('view', $certificate);

        // Prefer the DOMPDF-rendered copy tied to this print event (watermarked with
        // its copy number); fall back to the stored certificate version PDF only if the
        // copy render failed.
        $pdf = $event->pdfFile ?? $event->version?->pdfFile;

        if (! $pdf || ! Storage::disk($pdf->storage_disk)->exists($pdf->object_key)) {
            abort(404, 'The PDF for this print copy is not available.');
        }

        app(\App\Services\AuditService::class)->recordSensitiveAccess(
            Certificate::class,
            $certificate->id,
            'certificate_pdf',
            'download',
            'Authorised print copy download',
        );

        return Storage::disk($pdf->storage_disk)->download(
            $pdf->object_key,
            $certificate->certificate_number.'-copy-'.str_pad((string) $event->print_number, 2, '0', STR_PAD_LEFT).'.pdf'
        );
    }

    public function suspend(Certificate $certificate, Request $request, CertificateStatusService $statuses)
    {
        $this->authorize('changeStatus', $certificate);

        $data = $request->validate([
            'reason_code' => ['required', 'string', 'max:60'],
            'reason_text' => ['required', 'string', 'max:2000'],
        ]);

        $statuses->suspend($certificate, auth()->user(), $data['reason_code'], $data['reason_text']);

        return redirect()->route('certificates.show', $certificate)->with('status', 'Certificate suspended.');
    }

    public function reinstate(Certificate $certificate, Request $request, CertificateStatusService $statuses)
    {
        $this->authorize('changeStatus', $certificate);

        $data = $request->validate([
            'reason_code' => ['required', 'string', 'max:60'],
            'reason_text' => ['required', 'string', 'max:2000'],
        ]);

        $statuses->reinstate($certificate, auth()->user(), $data['reason_code'], $data['reason_text']);

        return redirect()->route('certificates.show', $certificate)->with('status', 'Certificate reinstated.');
    }

    public function revoke(Certificate $certificate, Request $request, CertificateStatusService $statuses)
    {
        $this->authorize('changeStatus', $certificate);

        $data = $request->validate([
            'reason_code' => ['required', 'string', 'max:60'],
            'reason_text' => ['required', 'string', 'max:2000'],
        ]);

        $statuses->revoke($certificate, auth()->user(), $data['reason_code'], $data['reason_text']);

        return redirect()->route('certificates.show', $certificate)->with('status', 'Certificate revoked. Public verification now shows REVOKED.');
    }

    public function reissue(Certificate $certificate, Request $request, CertificateRenderService $renderer)
    {
        $this->authorize('changeStatus', $certificate);

        $data = $request->validate([
            'reason_code' => ['required', 'string', 'max:60'],
            'reason_text' => ['required', 'string', 'max:2000'],
        ]);

        $new = $renderer->reissue($certificate, auth()->user(), $data['reason_code'], $data['reason_text']);

        return redirect()->route('certificates.show', $new)
            ->with('status', 'Reissue started. Issue the new certificate to publish it.');
    }
}
