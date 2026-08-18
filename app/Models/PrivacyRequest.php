<?php

namespace App\Models;

use App\Models\Concerns\UsesUuidV7;
use Illuminate\Database\Eloquent\Model;

class PrivacyRequest extends Model
{
    use UsesUuidV7;

    protected $fillable = [
        'reference_number', 'indigene_id', 'requester_identity_ciphertext', 'request_type', 'channel',
        'received_at', 'verification_status', 'status', 'assigned_to', 'due_at', 'lawful_exception',
        'decision', 'response_file_id', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function indigene()
    {
        return $this->belongsTo(Indigene::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function requestTypeLabel(): string
    {
        return str_replace('_', ' ', ucfirst($this->request_type));
    }
}
