@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Application, security, certificate and system notices. Notification text never contains a NIN.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Notifications</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-end mb-3">
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary rounded-3 fw-semibold" type="submit">
                        <i class="ri-check-double-line me-1"></i> Mark all read
                    </button>
                </form>
            </div>

            @forelse ($notifications as $notification)
                <div class="d-flex justify-content-between align-items-start py-3 border-bottom {{ is_null($notification->read_at) ? 'bg-light-subtle' : '' }}">
                    <div class="d-flex gap-3">
                        <span class="material-symbols-outlined {{ $notification->data['icon_class'] ?? 'text-primary' }}">
                            {{ $notification->data['icon'] ?? 'sms' }}
                        </span>
                        <div>
                            @if (isset($notification->data['link']))
                                <a href="{{ $notification->data['link'] }}" class="fw-semibold text-decoration-none">
                                    {{ $notification->data['message'] ?? 'Notification' }}
                                </a>
                            @else
                                <span class="fw-semibold">{{ $notification->data['message'] ?? 'Notification' }}</span>
                            @endif
                            <div class="small text-secondary">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @if (is_null($notification->read_at))
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button class="btn btn-sm btn-link text-secondary" type="submit">Mark read</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="empty-state">
                    <span class="material-symbols-outlined">notifications_off</span>
                    <p class="mb-1 fw-semibold">No notifications</p>
                    <p class="small mb-0">You are all caught up.</p>
                </div>
            @endforelse

            @include('partials.pagination')
        </div>
    </div>
@endsection
