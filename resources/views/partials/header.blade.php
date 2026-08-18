<header class="header-area bg-white mb-4 rounded-bottom-15" id="header-area">
    <div class="row align-items-center">
        <div class="col-lg-5 col-sm-6">
            <div class="left-header-content">
                <ul class="d-flex align-items-center ps-0 mb-0 list-unstyled justify-content-center justify-content-sm-start">
                    <li>
                        <button class="header-burger-menu bg-transparent p-0 border-0" id="header-burger-menu">
                            <span class="material-symbols-outlined">menu</span>
                        </button>
                    </li>
                    <li>
                        <form class="src-form position-relative" method="GET" action="{{ route('indigenes.search') }}">
                            <input type="text" class="form-control" name="q" value="{{ request('q') }}"
                                   placeholder="Search registry, certificate, application&hellip;" aria-label="Global search" />
                            <button type="submit" class="src-btn position-absolute top-50 end-0 translate-middle-y bg-transparent p-0 border-0">
                                <span class="material-symbols-outlined">search</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-lg-7 col-sm-6">
            <div class="right-header-content mt-2 mt-sm-0">
                <ul class="d-flex align-items-center justify-content-center justify-content-sm-end ps-0 mb-0 list-unstyled">
                    <li class="header-right-item d-none d-md-block">
                        <div class="role-lga-chip d-flex align-items-center gap-2">
                            <span class="badge-role badge bg-primary-div bg-opacity-10 text-primary-div rounded-pill px-3 py-2 fs-13 fw-semibold">
                                <i class="ri-user-settings-line me-1"></i>{{ auth()->user()->primaryRoleName() }}
                            </span>
                            @if (auth()->user()->activeLga())
                                <span class="badge-lga badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2 fs-13 fw-semibold">
                                    <i class="ri-map-pin-line me-1"></i>{{ auth()->user()->activeLga()->name }}
                                </span>
                            @endif
                        </div>
                    </li>
                    <li class="header-right-item">
                        <div class="dropdown notifications noti">
                            <button class="btn btn-secondary border-0 p-0 position-relative badge" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                                <span class="material-symbols-outlined">notifications</span>
                                @if ($unreadNotifications = $unreadNotifications ?? null)
                                    <span class="badge-notification">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                                @endif
                            </button>
                            <div class="dropdown-menu dropdown-lg p-0 border-0 dropdown-menu-end">
                                <div class="d-flex justify-content-between align-items-center title">
                                    <span class="fw-semibold fs-15 text-secondary">
                                        Notifications
                                        <span class="fw-normal text-body fs-14">({{ $unreadNotifications ?? 0 }})</span>
                                    </span>
                                </div>
                                <div class="max-h-217" data-simplebar>
                                    @forelse ($headerNotifications = $headerNotifications ?? collect() as $notification)
                                        <div class="notification-menu {{ is_null($notification->read_at) ? 'unseen' : '' }}">
                                            <a href="{{ $notification->data['link'] ?? route('notifications.index') }}" class="dropdown-item">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <i class="material-symbols-outlined {{ $notification->data['icon_class'] ?? 'text-primary' }}">{{ $notification->data['icon'] ?? 'sms' }}</i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="mb-1">{{ $notification->data['message'] ?? 'New notification' }}</p>
                                                        <span class="fs-13">{{ $notification->created_at->diffForHumans() }}</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="notification-menu text-center py-4">
                                            <span class="fs-14 text-secondary">No notifications yet.</span>
                                        </div>
                                    @endforelse
                                </div>
                                <a href="{{ route('notifications.index') }}" class="dropdown-item text-center text-primary d-block view-all fw-medium rounded-bottom-3">
                                    <span>See All Notifications</span>
                                </a>
                            </div>
                        </div>
                    </li>
                    <li class="header-right-item">
                        <div class="dropdown admin-profile">
                            <div class="d-xxl-flex align-items-center bg-transparent border-0 text-start p-0 cursor dropdown-toggle" data-bs-toggle="dropdown">
                                <div class="flex-shrink-0">
                                    <span class="avatar-initials rounded-circle wh-40 d-flex align-items-center justify-content-center bg-primary-div bg-opacity-10 text-primary-div fw-bold">
                                        {{ strtoupper(substr(auth()->user()->full_name ?? 'A', 0, 1)) }}
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <div class="d-none d-xxl-block">
                                        <div class="d-flex align-content-center">
                                            <h3>{{ Str::limit(auth()->user()->full_name ?? 'User', 16) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="dropdown-menu border-0 bg-white dropdown-menu-end">
                                <div class="d-flex align-items-center info">
                                    <div class="flex-shrink-0">
                                        <span class="avatar-initials rounded-circle wh-30 d-flex align-items-center justify-content-center bg-primary-div bg-opacity-10 text-primary-div fw-bold">
                                            {{ strtoupper(substr(auth()->user()->full_name ?? 'A', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <h3 class="fw-medium">{{ auth()->user()->full_name ?? 'User' }}</h3>
                                        <span class="fs-12">{{ auth()->user()->primaryRoleName() }}@if (auth()->user()->activeLga()) &middot; {{ auth()->user()->activeLga()->name }}@endif</span>
                                    </div>
                                </div>
                                <ul class="admin-link ps-0 mb-0 list-unstyled">
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center text-body" href="{{ route('profile.show') }}">
                                            <i class="material-symbols-outlined">account_circle</i>
                                            <span class="ms-2">My Profile</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center text-body" href="{{ route('notifications.index') }}">
                                            <i class="material-symbols-outlined">notifications</i>
                                            <span class="ms-2">Notifications</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center text-body" href="{{ route('help.index') }}">
                                            <i class="material-symbols-outlined">support</i>
                                            <span class="ms-2">Help Centre</span>
                                        </a>
                                    </li>
                                </ul>
                                <ul class="admin-link ps-0 mb-0 list-unstyled">
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item d-flex align-items-center text-body border-0 bg-transparent w-100">
                                                <i class="material-symbols-outlined">logout</i>
                                                <span class="ms-2">Logout</span>
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
