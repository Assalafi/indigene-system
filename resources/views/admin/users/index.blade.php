@extends('layouts.app')

@section('title', 'Staff Users')
@section('page-title', 'Staff Users')
@section('page-subtitle', 'All staff accounts and their LGA assignments. NIN is never a staff-account field.')

@section('breadcrumbs')
    <li class="breadcrumb-item active" aria-current="page"><span class="fw-medium">Users</span></li>
@endsection

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary-div text-white px-4 py-2 rounded-3 fw-semibold">
            <i class="ri-user-add-line me-1"></i> Create user
        </a>
    </div>

    <div class="card bg-white border-0 rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input class="form-control" type="text" name="q" placeholder="Search name or email&hellip;" value="{{ request('q') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All statuses</option>
                        @foreach (['invited', 'active', 'suspended', 'locked'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="role">
                        <option value="">All roles</option>
                        @foreach (\App\Models\Role::orderBy('name')->get() as $role)
                            <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary-div text-white w-100" type="submit"><i class="ri-filter-line"></i></button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Name</th><th>Email</th><th>Phone</th><th>LGA</th><th>Status</th><th>Last login</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $staff)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $staff->full_name }}</span>
                                    <div class="small text-secondary">{{ optional($staff->roles->first())->name }}</div>
                                </td>
                                <td>{{ $staff->email }}</td>
                                <td>{{ $staff->phone ?? '—' }}</td>
                                <td>{{ optional($staff->activeAssignments->first())->lga?->name ?? '—' }}</td>
                                <td>
                                    @include('partials.status-badge', ['status' => match($staff->status) {
                                        'invited' => 'draft', 'active' => 'approved', 'suspended' => 'suspended', 'locked' => 'rejected', default => 'draft',
                                    }])
                                </td>
                                <td>{{ optional($staff->last_login_at)->format('d/m/Y H:i') ?? 'Never' }}</td>
                                <td><a href="{{ route('admin.users.show', $staff) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7">
                                <div class="empty-state">
                                    <span class="material-symbols-outlined">manage_accounts</span>
                                    <p class="mb-1 fw-semibold">No staff users found</p>
                                    <p class="small mb-0">Create the first user to begin.</p>
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
