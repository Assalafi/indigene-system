<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class FraudReport extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'certificate_id', 'reference_number', 'reporter_name_ciphertext', 'reporter_contact_ciphertext',
        'report_text', 'evidence_file_id', 'status', 'assigned_to', 'resolution', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
