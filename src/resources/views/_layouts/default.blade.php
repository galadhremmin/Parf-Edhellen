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
  <meta property="og:title" content="@yield('title') - {{ config('ed.title') }}">
  <meta property="og:description" content="{{ config('ed.description') }}">
  <meta property="og:locale" content="{{ config('ed.view_locale') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="{{ config('ed.description') }}">
  <meta name="keywords" content="sindarin, quenya, noldorin, quendya, elvish, tolkien, nandorin, ilkorin, black speech, westron">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="theme-color" content="#333333" media="(prefers-color-scheme: light)">
  <meta name="theme-color" content="#1a1a2e" media="(prefers-color-scheme: dark)">
  <meta name="google" content="notranslate"> {{-- Remedies 'Failed to execute 'removeChild' on 'Node': The node to be removed is not a child of this node' --}}
  <link rel="apple-touch-icon-precomposed" href="/img/favicons/apple-touch-icon-precomposed.png">
  <link rel="icon" type="image/png" href="/img/favicons/favicon-194x194.png">
  <link rel="manifest" href="/img/favicons/manifest.json">
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
<div>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark" id="ed-site-main-menu">
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
<footer class="bg-dark text-secondary p-4 d-flex">
  <section class="flex-fill w-100">
    <h3 class="fst-italic fs-5">{{ config('ed.title') }}</h3>
    <nav>
      <ul>
        <li><a href="{{ route('login') }}" class="link-secondary text-decoration-underline">Sign in</a></li>
        <li><a href="{{ route('about') }}" class="link-secondary text-decoration-underline">About the website</a></li>
        <li><a href="{{ route('about.cookies') }}" class="link-secondary text-decoration-underline">Cookie policy</a></li>
        <li><a href="{{ route('about.privacy') }}" class="link-secondary text-decoration-underline">Privacy policy</a></li>
      </ul>
    </nav>
  </section>
  <section class="flex-fill w-100">
    Black Speech, Nandorin, Noldorin, Quendya, Quenya, Sindarin, Telerin are languages conceived by Tolkien and they do not belong to us; 
    we neither can nor do claim affiliation with <a href="http://www.middleearth.com/" target="_blank" class="link-secondary text-decoration-underline">Middle-earth Enterprises</a> nor 
    <a href="http://www.tolkienestate.com/" target="_blank" class="link-secondary text-decoration-underline">Tolkien Estate</a>.
  </section>
</footer>

<div id="ed-eu-consent"></div>

<script type="text/javascript" src="@assetpath(index.js)"></script>

@yield('scripts')
@if (!empty(config('ed.footer_view')))
  @include(config('ed.footer_view'))
@endif
</body>
</html>
