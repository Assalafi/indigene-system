<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="@yield('meta-description', $metaDescription)">
        <meta name="keywords" content="{{ $metaKeywords }}">
        <meta name="author" content="{{ $metaAuthor }}">
        <meta property="og:title" content="{{ $metaOgTitle }}">
        <meta property="og:description" content="{{ $metaOgDescription }}">
        <meta property="og:type" content="website">

        <title>@yield('title', $brandShortName) | {{ $brandShortName }}</title>
        @include('partials.styles')
        @stack('styles')
    </head>
    <body data-bs-spy="scroll" data-bs-target="#navbar" data-bs-root-margin="0px 0px -40%" data-bs-smooth-scroll="true" class="scrollspy-example" tabindex="0">
        @include('partials.preloader')
        @include('partials.navbar')

        <main id="main-content">
            @yield('content')
        </main>

        @include('partials.public-footer')
        @include('partials.scripts')
    </body>
</html>
