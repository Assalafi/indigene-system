<?php

namespace App\Http\Controllers;

class HelpController extends Controller
{
    public function index()
    {
        $guides = [
            ['icon' => 'note_add', 'title' => 'Create an application', 'text' => 'Use the eight-step wizard. Drafts autosave after a short idle period and can be resumed from the Applications screen.'],
            ['icon' => 'edit_note', 'title' => 'Correct a submission', 'text' => 'Open the application with "Correction required" status, adjust the highlighted fields, and resubmit. The earlier version stays immutable.'],
            ['icon' => 'how_to_vote', 'title' => 'Approve an application', 'text' => 'Open the Approval Queue, review the checklist, documents and duplicate flags, then record your decision. You cannot approve an application you created.'],
            ['icon' => 'print', 'title' => 'Print or reprint', 'text' => 'Open an approved certificate and choose "Generate print copy". Reprints require a reason and are numbered COPY 02, 03 and so on.'],
            ['icon' => 'qr_code_scanner', 'title' => 'Verify a certificate', 'text' => 'Use the public Verify page or scan the QR code. The result shows VALID, SUSPENDED, SUPERSEDED or REVOKED - never private applicant data.'],
            ['icon' => 'report', 'title' => 'Report an incident', 'text' => 'Contact your System Administrator or use the support form. Suspected fraudulent certificates can be reported publicly on the verification pages.'],
        ];

        return view('help.index', compact('guides'));
    }
}
