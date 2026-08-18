<div class="sidebar-area" id="sidebar-area">
    <div class="logo position-relative">
        @if ($brandLogoUrl ?? null)
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none position-relative">
                <img src="{{ $brandLogoUrl }}" alt="{{ $brandShortName ?? 'NIMCS' }} logo" class="nimcs-logo-img nimcs-sidebar-logo">
                <span class="logo-text fw-bold text-dark" style="position:static;top:auto;left:auto;font-size:18px;">{{ $brandShortName ?? 'NIMCS' }}</span>
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="d-block text-decoration-none position-relative">
                <img src="/assets/images/nimcs-logo-icon.png" alt="logo-icon" onerror="this.style.display='none'">
                <span class="logo-text fw-bold text-dark">{{ $brandShortName ?? 'NIMCS' }}</span>
            </a>
        @endif
        <button class="sidebar-burger-menu bg-transparent p-0 border-0 opacity-0 z-n1 position-absolute top-50 end-0 translate-middle-y" id="sidebar-burger-menu">
            <i data-feather="x"></i>
        </button>
    </div>

    <aside id="layout-menu" class="layout-menu menu-vertical menu active" data-simplebar>
        <ul class="menu-inner">
            <li class="menu-title small text-uppercase">
                <span class="menu-title-text">MAIN</span>
            </li>
            <li class="menu-item">
                <a href="{{ route('dashboard') }}" class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">dashboard</span>
                    <span class="title">Dashboard</span>
                </a>
            </li>

            <li class="menu-item {{ request()->routeIs('applications.*', 'approvals.*', 'duplicates.*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle {{ request()->routeIs('applications.*', 'approvals.*', 'duplicates.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">description</span>
                    <span class="title">Applications</span>
                    @if ($pendingReviewCount = $pendingReviewCount ?? null)
                        <span class="count">{{ $pendingReviewCount }}</span>
                    @endif
                </a>
                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ route('applications.index') }}" class="menu-link {{ request()->routeIs('applications.index') ? 'active' : '' }}">
                            All Applications
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('applications.create') }}" class="menu-link {{ request()->routeIs('applications.create') ? 'active' : '' }}">
                            New Application
                        </a>
                    </li>
                    @can('application.decide')
                        <li class="menu-item mb-0">
                            <a href="{{ route('approvals.queue') }}" class="menu-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                                Approval Queue
                            </a>
                        </li>
                    @endcan
                    @can('application.review-duplicates')
                        <li class="menu-item mb-0">
                            <a href="{{ route('duplicates.index') }}" class="menu-link {{ request()->routeIs('duplicates.*') ? 'active' : '' }}">
                                Duplicate Review
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>

            <li class="menu-item {{ request()->routeIs('indigenes.*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle {{ request()->routeIs('indigenes.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">groups</span>
                    <span class="title">Indigenes</span>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ route('indigenes.index') }}" class="menu-link {{ request()->routeIs('indigenes.index') ? 'active' : '' }}">
                            Registry
                        </a>
                    </li>
                    <li class="menu-item mb-0">
                        <a href="{{ route('indigenes.search') }}" class="menu-link {{ request()->routeIs('indigenes.search') ? 'active' : '' }}">
                            Search
                        </a>
                    </li>
                </ul>
            </li>

            <li class="menu-item {{ request()->routeIs('certificates.*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle {{ request()->routeIs('certificates.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">verified</span>
                    <span class="title">Certificates</span>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ route('certificates.index') }}" class="menu-link {{ request()->routeIs('certificates.index') ? 'active' : '' }}">
                            All Certificates
                        </a>
                    </li>
                    <li class="menu-item mb-0">
                        <a href="{{ route('certificates.print-history') }}" class="menu-link {{ request()->routeIs('certificates.print-history') ? 'active' : '' }}">
                            Print History
                        </a>
                    </li>
                </ul>
            </li>

            @if (auth()->user()->can('geography.view') || auth()->user()->can('geography.manage-local'))
                <li class="menu-item {{ request()->routeIs('geography.*') ? 'open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle {{ request()->routeIs('geography.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined menu-icon">map</span>
                        <span class="title">Geography</span>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item">
                            <a href="{{ route('geography.states') }}" class="menu-link {{ request()->routeIs('geography.states') ? 'active' : '' }}">
                                States &amp; LGAs
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('geography.wards') }}" class="menu-link {{ request()->routeIs('geography.wards') ? 'active' : '' }}">
                                Wards &amp; Units
                            </a>
                        </li>
                        @can('geography.import')
                            <li class="menu-item mb-0">
                                <a href="{{ route('geography.imports.index') }}" class="menu-link {{ request()->routeIs('geography.imports.*') ? 'active' : '' }}">
                                    Import Data
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif

            <li class="menu-item {{ request()->routeIs('reports.*', 'exports.*') ? 'open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle {{ request()->routeIs('reports.*', 'exports.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">monitoring</span>
                    <span class="title">Reports</span>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ route('reports.index') }}" class="menu-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                            Report Catalogue
                        </a>
                    </li>
                    <li class="menu-item mb-0">
                        <a href="{{ route('reports.exports') }}" class="menu-link {{ request()->routeIs('reports.exports') ? 'active' : '' }}">
                            My Exports
                        </a>
                    </li>
                </ul>
            </li>

            @can('user.manage')
                <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined menu-icon">manage_accounts</span>
                        <span class="title">Users</span>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item">
                            <a href="{{ route('admin.users.index') }}" class="menu-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                                Staff Users
                            </a>
                        </li>
                        <li class="menu-item mb-0">
                            <a href="{{ route('admin.users.create') }}" class="menu-link {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                                Create User
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            @can('audit.view')
                <li class="menu-item {{ request()->routeIs('audit.*') ? 'open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined menu-icon">shield_person</span>
                        <span class="title">Audit &amp; Security</span>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item">
                            <a href="{{ route('audit.index') }}" class="menu-link {{ request()->routeIs('audit.index') ? 'active' : '' }}">
                                Audit Log
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="{{ route('audit.sensitive-access') }}" class="menu-link {{ request()->routeIs('audit.sensitive-access') ? 'active' : '' }}">
                                Sensitive Access
                            </a>
                        </li>
                        <li class="menu-item mb-0">
                            <a href="{{ route('audit.login-events') }}" class="menu-link {{ request()->routeIs('audit.login-events') ? 'active' : '' }}">
                                Login Events
                            </a>
                        </li>
                    </ul>
                </li>
            @endcan

            @if (auth()->user()->can('privacy.manage') || auth()->user()->can('privacy.view'))
                <li class="menu-item {{ request()->routeIs('privacy.*') ? 'open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle {{ request()->routeIs('privacy.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined menu-icon">privacy_tip</span>
                        <span class="title">Privacy</span>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item">
                            <a href="{{ route('privacy.requests.index') }}" class="menu-link {{ request()->routeIs('privacy.requests.*') ? 'active' : '' }}">
                                Data Requests
                            </a>
                        </li>
                        <li class="menu-item mb-0">
                            <a href="{{ route('privacy.holds.index') }}" class="menu-link {{ request()->routeIs('privacy.holds.*') ? 'active' : '' }}">
                                Legal Holds
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            @if (auth()->user()->can('settings.view'))
                <li class="menu-item {{ request()->routeIs('settings.*', 'admin.lga-profiles.*', 'admin.signatories.*', 'admin.templates.*') ? 'open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle {{ request()->routeIs('settings.*', 'admin.lga-profiles.*', 'admin.signatories.*', 'admin.templates.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined menu-icon">settings</span>
                        <span class="title">Settings</span>
                    </a>
                    <ul class="menu-sub">
                        @can('settings.view')
                            <li class="menu-item">
                                <a href="{{ route('settings.index') }}" class="menu-link {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                                    Global Settings
                                </a>
                            </li>
                        @endcan
                        @can('lga-profile.manage')
                            <li class="menu-item mb-0">
                                <a href="{{ route('admin.lga-profiles.index') }}" class="menu-link {{ request()->routeIs('admin.lga-profiles.*') ? 'active' : '' }}">
                                    LGA Profiles &amp; Signatories
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif

            <li class="menu-title small text-uppercase">
                <span class="menu-title-text">OTHERS</span>
            </li>

            <li class="menu-item">
                <a href="{{ route('notifications.index') }}" class="menu-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">notifications</span>
                    <span class="title">Notifications</span>
                    @if ($unreadNotifications = $unreadNotifications ?? null)
                        <span class="count">{{ $unreadNotifications }}</span>
                    @endif
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('help.index') }}" class="menu-link {{ request()->routeIs('help.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">support</span>
                    <span class="title">Help</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="{{ route('profile.show') }}" class="menu-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">account_circle</span>
                    <span class="title">My Profile</span>
                </a>
            </li>

            <li class="menu-item">
                <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form" class="d-none">
                    @csrf
                </form>
                <a href="javascript:void(0);" class="menu-link logout" onclick="document.getElementById('sidebar-logout-form').submit()">
                    <span class="material-symbols-outlined menu-icon">logout</span>
                    <span class="title">Logout</span>
                </a>
            </li>
        </ul>
    </aside>
</div>
