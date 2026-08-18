<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="@yield('meta-description', $metaDescription)">
        <meta name="author" content="{{ $metaAuthor }}">

        <title>@yield('title', 'Staff Portal') | {{ $brandShortName }}</title>
        @include('partials.styles')
    </head>
    <body class="boxed-size">
        @include('partials.preloader')
        @include('partials.sidebar')

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                        <div>
                            <h3 class="mb-1">@yield('page-title', 'Dashboard')</h3>
                            @hasSection('page-subtitle')
                                <p class="page-subtitle mb-0">@yield('page-subtitle')</p>
                            @endif
                        </div>

                        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                            <ol class="breadcrumb align-items-center mb-0 lh-1">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none">
                                        <i class="ri-home-4-line fs-18 text-primary me-1"></i>
                                        <span class="text-secondary fw-medium hover">Dashboard</span>
                                    </a>
                                </li>
                                @hasSection('breadcrumbs')
                                    @yield('breadcrumbs')
                                @endif
                            </ol>
                        </nav>
                    </div>

                    @include('partials.flash-messages')

                    @yield('content')
                </div>

                <div class="flex-grow-1"></div>

                @include('partials.footer')
            </div>
        </div>

        @include('partials.scripts')
    </body>
</html>
