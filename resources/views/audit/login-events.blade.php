@extends('layouts.app')

@section('title', 'Login Events')
@section('page-title', 'Login Events')
@section('page-subtitle', 'Successful and failed logins plus privileged re-authentication.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Login Events</span></li>
@endsection

@section('content')
    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('audit.login-events') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input class="form-control" type="text" name="event_type" placeholder="Filter by event type&hellip;" value="{{ request('event_type') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="success">
                        <option value="">All outcomes</option>
                        <option value="1" @selected(request('success') === '1')>Success only</option>
                        <option value="0" @selected(request('success') === '0')>Failures only</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary-div text-white w-100" type="submit"><i class="ri-filter-line"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Time</th><th>User</th><th>Event type</th><th>Success</th><th>Risk flags</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                            <tr>
                                <td>{{ $event->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ optional($event->user)->full_name ?? optional($event->user)->email ?? 'Unknown identity' }}</td>
                                <td>{{ $event->event_type }}</td>
                                <td>@include('partials.status-badge', ['status' => $event->success ? 'approved' : 'rejected'])</td>
                                <td>
                                    @if ($event->risk_flags)
                                        @foreach ($event->risk_flags as $flag)
                                            <span class="risk-chip medium">{{ $flag }}</span>
                                        @endforeach
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">login</span>
                                    <p class="mb-1 fw-semibold">No login events</p>
                                    <p class="small mb-0">Sign-in activity appears here.</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('partials.pagination')
        </div>
    </div>
@endsection
