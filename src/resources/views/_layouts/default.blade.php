<!DOCTYPE html>
<html lang="{{ config('ed.view_locale') }}" prefix="og: http://ogp.me/ns#">
<head>
  <title>@yield('title') - {{ config('ed.title') }}</title>
  <meta charset="UTF-8">
  <meta property="og:title" content="@yield('title') - {{ config('ed.title') }}">
  <meta property="og:description" content="{{ config('ed.description') }}">
  <meta property="og:locale" content="{{ config('ed.view_locale') }}">
  <meta name="description" content="{{ config('ed.description') }}">
  <meta name="keywords" content="sindarin, quenya, noldorin, quendya, elvish, tolkien, nandorin, ilkorin, black speech, westron">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="theme-color" value="#333333">
  <meta name="csrf-token" id="ed-csrf-token" content="{{ csrf_token() }}">
  <link rel="apple-touch-icon-precomposed" href="/img/favicons/apple-touch-icon-precomposed.png">
  <link rel="icon" type="image/png" href="/img/favicons/favicon-194x194.png">
  <link rel="manifest" href="/img/favicons/manifest.json">
  <link href="@assetpath(/index.css)" rel="stylesheet">
  @yield('styles')
  @if (!empty(config('ed.header_view')))
    @include(config('ed.header_view'))
  @endif
  <noscript><style type="text/css">.noscript--hidden{display:none;}</style></noscript>
</head>
<body class="bg-dark {{ $isAdmin ? 'ed-admin' : ($isAdmin === false ? 'ed-user' : 'ed-anonymous') }}" data-account-id="{{ $user ? $user->id : '0' }}" data-v="{{ config('ed.version') }}">
<div class="bg-white pb-4">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="/">{{ config('ed.title') }}</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-menu-content" aria-controls="main-menu-content" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="main-menu-content">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link {{ active('home') }}" href="/">@lang('home.title')</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ active('about') }}" href="{{ route('about') }}">@lang('about.title')</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ active(['sentence.public', 'sentence.public.language', 'sentence.public.sentence']) }}" href="{{ route('sentence.public') }}">@lang('sentence.title')</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ active(['games', 'flashcard', 'flashcard.cards', 'word-finder.index', 'word-finder.show']) }}" href="{{ route('games') }}">@lang('games.title')</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ active(['discuss.index', 'discuss.group', 'discuss.show', 'discuss.member-list']) }}" href="{{ route('discuss.index') }}">
              @lang('discuss.title')
            </a>
          </li>
        </ul>
        <ul class="navbar-nav d-flex mb-2 mb-lg-0">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="user-menu-dropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              @if ($user)
                {{ $user->nickname }}
              @else
                @lang('discuss.community.title')
              @endif
            </a>
            <ul class="dropdown-menu" aria-labelledby="user-menu-dropdown">
              @if ($user)
              <li>
                <a class="dropdown-item {{ active('dashboard') }}" href="{{ route('dashboard') }}">
                  <span class="glyphicon glyphicon-dashboard"></span> 
                  &nbsp;@lang('dashboard.title')
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ active('author.my-profile') }}" href="{{ route('author.my-profile') }}">
                  <span class="glyphicon glyphicon-user"></span> 
                  &nbsp;@lang('discuss.community.profile')
                </a>
              </li>
              <li>
                <a class="dropdown-item {{ active('contribution.index') }}" href="{{ route('contribution.index') }}">
                  <span class="glyphicon glyphicon-book"></span> 
                  &nbsp;@lang('dashboard.contributions')
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{ route('logout') }}">
                  <span class="glyphicon glyphicon-log-out"></span> 
                  &nbsp;@lang('dashboard.logout')
                </a>
              </li>
              @else
              <li>
                <a class="dropdown-item {{ active('login') }}" href="{{ route('login') }}">
                  <span class="glyphicon glyphicon-log-in"></span> 
                  &nbsp;@lang('dashboard.login')
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{ route('login') }}">
                  <span class="glyphicon glyphicon-user"></span> 
                  &nbsp;@lang('dashboard.register')
                </a>
              </li>
              @endif
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item {{ active('discuss.members') }}" href="{{ route('discuss.members') }}">
                  @lang('discuss.member-list.title')
                </a>
              </li>
            </ul>
          </li>
      </div>
    </div>
  </nav>
  <div class="head-content">
    <main class="container" id="site-container">
      <!-- scripting disabled warning -->
      <noscript>
        <div id="noscript" class="alert alert-danger">
          <strong><span class="glyphicon glyphicon-flag" aria-hidden="true"></span> @lang('home.noscript.title')</strong>
          <p>@lang('home.noscript.message', ['website' => config('ed.title')])</p>
          <p><a href="https://support.google.com/bin/answer.py?hl=en&amp;answer=23852" target="_blank">@lang('home.noscript.call-to-action')</a>.</p>
        </div>
      </noscript>

      <!-- search component -->
      <div id="ed-search-component" class="mt-4"></div>

      <!-- begin content -->
      @yield('body')
    </main>
  </div>
</div>
<footer class="text-muted p-4 d-flex">
  <section class="flex-fill w-100">
    <h3>{{ config('ed.title') }}</h3>
    <nav>
      <ul>
        <li><a href="{{ route('login') }}">Log in</a></li>
        <li><a href="{{ route('about') }}">About the website</a></li>
        <li><a href="{{ route('about.cookies') }}">Cookie policy</a></li>
        <li><a href="{{ route('about.privacy') }}">Privacy policy</a></li>
      </ul>
    </nav>
  </section>
  <section class="flex-fill w-100">
    Black Speech, Nandorin, Noldorin, Quendya, Quenya, Sindarin, Telerin are languages conceived by Tolkien and they do not belong to us; 
    we neither can nor do claim affiliation with <a href="http://www.middleearth.com/" target="_blank">Middle-earth Enterprises</a> nor 
    <a href="http://www.tolkienestate.com/" target="_blank">Tolkien Estate</a>.
  </section>
</footer>

<script type="text/javascript" src="@assetpath(index.js)"></script>

@yield('scripts')
@if (!empty(config('ed.footer_view')))
  @include(config('ed.footer_view'))
@endif
</body>
</html>
