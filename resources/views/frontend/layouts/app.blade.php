<!doctype html>
<html lang="{{ htmlLang() }}" @langrtl dir="rtl" @endlangrtl class="theme-dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ appName() }} | @yield('title')</title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="content-type" content="text/html, charset=utf-8">
    <meta name="description" content="@yield('meta_description', appName())">
    <meta name="author" content="@yield('meta_author', 'kantai.developer@gmail.com')">

    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="url" itemprop="url" content="{{ url()->current() }}">
    <link rel="alternate" href="{{ url()->current() }}" hreflang="x-default">

    <meta property="article:tag" content="Nursing Init Engineer">
    <meta property="article:tag" content="靠北護理師">

    <link rel="icon" href="/img/fluid/icon.jpeg">

    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:publisher" content="https://www.facebook.com/init.kobeengineer">
    <meta property="og:title" content="@yield('meta_title', appName())">
    <meta property="og:description" content="@yield('meta_description', appName())">
    <meta property="og:image" content="@yield('meta_image', asset('/img/fluid/icon.jpeg'))">
    <meta property="og:image:alt" content="@yield('meta_description', appName())">
    <meta property="og:image:secure_url" content="@yield('meta_image', asset('/img/fluid/icon.jpeg'))">
    <meta property="og:type" content="@yield('meta_type', 'website')">
    <meta property="og:site_name" content="{{ appName() }}">
    <meta property="og:author" content="https://www.facebook.com/init.kobeengineer">
    <meta property="og:locale" content="zh_TW">

    <meta name="twitter:site" content="@InitEngineer">
    <meta name="twitter:title" content="@yield('meta_title', appName())">
    <meta name="twitter:description" content="@yield('meta_description', appName())">
    <meta name="twitter:image" content="@yield('meta_image', asset('/img/fluid/icon.jpeg'))">
    <meta name="twitter:image:src" content="@yield('meta_image', asset('/img/fluid/icon.jpeg'))">
    <meta name="twitter:card" content="summary_large_image">

    <meta name="application-name" content="{{ appName() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#293134">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="supported-color-schemes" content="dark light">

    @yield('meta')

    @stack('before-styles')
    @livewireStyles()
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">
    <link href="{{ mix('css/frontend.css') }}" rel="stylesheet">
    @stack('after-styles')

    @include('includes.partials.ga')
</head>
<body>
    @include('frontend.includes.github')
    @include('includes.partials.read-only')
    @include('includes.partials.logged-in-as')
    @include('includes.partials.announcements')

    <div id="app">
        @include('frontend.includes.nav')
        @if (config('boilerplate.frontend_breadcrumbs'))
            @include('frontend.includes.partials.breadcrumbs')
        @endif
        @include('includes.partials.messages')

        <main>
            @yield('content')
        </main>
        <!--main-->

        @include('frontend.includes.footer')
    </div>
    <!--app-->

    @stack('before-scripts')
    @livewireScripts()
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/frontend.js') }}"></script>
    @stack('after-scripts')
</body>
</html>
