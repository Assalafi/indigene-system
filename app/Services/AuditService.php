<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

/**
 * SRD 29.1 - consistent append-only audit event capture.
 * Sensitive values are redacted before storage.
 */
class AuditService
{
    public function record(
        string $action,
        ?string $auditableType = null,
        ?string $auditableId = null,
        array $before = [],
        array $after = [],
        string $riskLevel = 'low',
        ?User $actor = null,
        string $result = 'success',
    ): AuditLog {
        $actor = $actor ?? auth()->user();
        $request = request();

        $before = $this->redact($before);
        $after = $this->redact($after);

        $previousHash = AuditLog::latest('occurred_at')->value('event_hash');

        $log = AuditLog::create([
            'actor_id' => $actor?->id,
            'actor_type' => $actor ? 'user' : 'system',
            'actor_role' => $actor?->roles()->first()?->name,
            'actor_lga_id' => $actor?->activeLga()?->id,
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'request_id' => $request?->header('X-Request-ID'),
            'route_name' => $request?->route()?->getName(),
            'http_method' => $request?->method(),
            'result' => $result,
            'risk_level' => $riskLevel,
            'before_values' => $before,
            'after_values' => $after,
            'ip_hash' => $request ? hash('sha256', $request->ip().'|'.config('app.key')) : null,
            'user_agent' => $request?->userAgent(),
        ]);

        $log->previous_hash = $previousHash;
        $log->event_hash = hash('sha256', $log->id.'|'.$action.'|'.($auditableId ?? '').'|'.$log->occurred_at?->toISOString());
        $log->save();

        return $log;
    }

    public function recordSensitiveAccess(string $subjectType, string $subjectId, string $category, string $action, string $purpose, string $result = 'success'): void
    {
        \App\Models\SensitiveDataAccessLog::create([
            'actor_id' => auth()->id(),
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'data_category' => $category,
            'action' => $action,
            'purpose' => $purpose,
            'result' => $result,
            'ip_hash' => request() ? hash('sha256', request()->ip().'|'.config('app.key')) : null,
        ]);
    }

    private function redact(array $values): array
    {
        $sensitiveKeys = ['nin', 'nin_ciphertext', 'password', 'token', 'recovery_codes'];

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = $this->redact($value);
                continue;
            }

            foreach ($sensitiveKeys as $sensitive) {
                if (stripos((string) $key, $sensitive) !== false) {
                    $values[$key] = '[REDACTED]';
                    break;
                }
            }
        }

        return $values;
    }
}
