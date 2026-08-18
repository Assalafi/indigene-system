@php
    $status = $status ?? 'draft';
    $label = \App\Enums\ApplicationStatus::tryFrom((string) $status)?->label() ?? ucfirst(str_replace('_', ' ', (string) $status));
    $icons = [
        'draft' => 'draft', 'submitted' => 'send', 'pending_chairman' => 'hourglass_top',
        'pending_system_admin' => 'hourglass_top', 'changes_requested' => 'edit_note',
        'approved' => 'check_circle', 'rejected' => 'cancel', 'suspended' => 'pause_circle',
        'revoked' => 'block', 'superseded' => 'swap_horiz', 'active' => 'verified', 'eligible' => 'pending',
    ];
    $icon = $icons[(string) $status] ?? 'info';
@endphp
<span class="status-badge status-{{ $status }}">
    <span class="material-symbols-outlined">{{ $icon }}</span>
    {{ $label }}
</span>
