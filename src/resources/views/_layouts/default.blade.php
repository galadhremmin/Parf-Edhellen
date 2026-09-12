@inject('cookie', 'App\Helpers\CookieHelper')
@inject('storage', 'App\Helpers\StorageHelper')

<!DOCTYPE html>
<html lang="{{ config('ed.view_locale') }}" prefix="og: http://ogp.me/ns#">
<head>
  <script>
    (function(){
      var stored = localStorage.getItem('ed-theme');
      var t = (stored === 'dark' || stored === 'light') ? stored : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.dataset.bsTheme = t;
      document.documentElement.dataset.agThemeMode = t;
      window.edToggleTheme = function(){
        var next = document.documentElement.dataset.bsTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.bsTheme = next;
        document.documentElement.dataset.agThemeMode = next;
        localStorage.setItem('ed-theme', next);
        document.documentElement.dispatchEvent(new CustomEvent('ed-theme-changed', { detail: { theme: next } }));
      };
    })();
  </script>
  <title>@yield('title') - {{ config('ed.title') }}</title>
  <meta charset="UTF-8">
  <meta name="description" content="@yield('description', config('ed.description'))">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="canonical" href="@yield('canonical', url()->current())">
  <meta property="og:title" content="@yield('title') - {{ config('ed.title') }}">
  <meta property="og:description" content="@yield('description', config('ed.description'))">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:type" content="@yield('og_type', 'website')">
  <meta property="og:image" content="@yield('og_image', asset('/img/favicons/android-chrome-192x192.png'))">
  <meta property="og:locale" content="{{ config('ed.view_locale') }}">
  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="@yield('title') - {{ config('ed.title') }}">
  <meta name="twitter:description" content="@yield('description', config('ed.description'))">
  <meta name="twitter:image" content="@yield('og_image', asset('/img/favicons/android-chrome-192x192.png'))">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#333333" media="(prefers-color-scheme: light)">
  <meta name="theme-color" content="#1a1a2e" media="(prefers-color-scheme: dark)">
  <meta name="google" content="notranslate"> {{-- Remedies 'Failed to execute 'removeChild' on 'Node': The node to be removed is not a child of this node' --}}
  <link rel="apple-touch-icon-precomposed" href="/img/favicons/apple-touch-icon-precomposed.png">
  <link rel="icon" type="image/png" href="/img/favicons/favicon-194x194.png">
  <link rel="manifest" href="/img/favicons/manifest.json">
  {{-- The two faces every page sets text in. Preloaded because they are
       otherwise only discovered once index.css has parsed, which is late
       enough that they swap in after first paint. The italic and
       latin-extended subsets are deliberately NOT preloaded: most pages
       never use them, and an unused preload is wasted bandwidth.
       `crossorigin` is required even same-origin -- fonts fetch in CORS
       mode, and without it the preload is discarded and fetched twice. --}}
  <link rel="preload" href="@assetpath(fonts/eb-garamond-latin.woff2)" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="@assetpath(fonts/cormorant-garamond-latin.woff2)" as="font" type="font/woff2" crossorigin>
  <link href="@assetpath(/index.css)" rel="stylesheet">
  @yield('styles')
  @if (!empty(config('ed.header_view')))
    @include(config('ed.header_view'))
  @endif
</head>
<body class="@yield('body-class')"
  @if (auth()->check())
  data-account-id="{{ auth()->user()->id }}"
  data-account-roles="{{ auth()->user()->getAllRoles()->implode(',') }}"
  @else
  data-account-id="0"
  data-account-roles=""
  @endif
  data-v="{{ config('ed.version') }}">
<div id="ed-site-wrapper">
  <nav class="navbar navbar-expand-lg navbar-dark" id="ed-site-main-menu" data-ad-region="no-ads">
    @include('_layouts._menu-main', [
      'storage' => $storage
    ])
  </nav>
  <div id="ed-site-main">
    <main>
      <div class="{{ $containerClass ?? 'container' }}">
        <noscript>
          <div id="noscript" class="alert alert-danger">
            <strong><span class="TextIcon TextIcon--warning-sign" aria-hidden="true"></span> @lang('home.noscript.title')</strong>
            <p>@lang('home.noscript.message', ['website' => config('ed.title')])</p>
            <p><a href="https://support.google.com/bin/answer.py?hl=en&amp;answer=23852" target="_blank">@lang('home.noscript.call-to-action')</a>.</p>
          </div>
        </noscript>
        @yield('before-search')
        @ssr('book-browser', [], [
          'element' => 'div',
          'attributes' => [
            'id' => 'ed-search-component',
            'class' => 'mt-4 mb-4'
          ]
        ])
        @yield('body')
      </div>
    </main>
  </div>
</div>
@include('_layouts._footer')

<script type="text/javascript" src="@assetpath(runtime.js)"></script>
<script type="text/javascript" src="@assetpath(index.js)"></script>

@yield('scripts')
@if (!empty(config('ed.footer_view')))
  @include(config('ed.footer_view'))
@endif
</body>
</html>
