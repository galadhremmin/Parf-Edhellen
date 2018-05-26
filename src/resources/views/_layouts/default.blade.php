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
  <link rel="apple-touch-icon-precomposed" href="/img/favicons/apple-touch-icon-precomposed.png">
  <link rel="icon" type="image/png" href="/img/favicons/favicon-194x194.png">
  <link rel="manifest" href="/img/favicons/manifest.json">
  <link href="https://fonts.googleapis.com/css?family=Lora:400,400i&amp;subset=latin-ext" rel="stylesheet">
  <link href="@assetpath(/css/app.css)" rel="stylesheet">
  @yield('styles')
  @if (!empty(config('ed.header_view')))
    @include(config('ed.header_view'))
  @endif
</head>
<body class="{{ $isAdmin ? 'ed-admin' : ($isAdmin === false ? 'ed-user' : 'ed-anonymous') }}" data-user-id="{{ $user ? $user->id : '0' }}" data-v="{{ config('ed.version') }}">
  <div class="navbar navbar-default" role="navigation">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
          <span class="sr-only">@lang('home.menu.open')</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/">{{ config('ed.title') }}</a>
      </div>
      <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
          <li class="{{ active('') }}"><a href="/">@lang('home.title')</a></li>
          <li class="{{ active('about') }}"><a href="{{ route('about') }}">@lang('about.title')</a></li>
          <li class="{{ active('about.donations') }} hidden-sm"><a href="{{ route('about.donations') }}">@lang('donations.title')</a></li>
          <li class="{{ active(['sentence.public', 'sentence.public.language', 'sentence.public.sentence']) }}"><a href="{{ route('sentence.public') }}">@lang('sentence.title')</a></li>
          <li class="{{ active('discuss.index') }}"><a href="{{ route('discuss.index') }}">@lang('discuss.title')</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
          <li class="{{ active('discuss.members') }}">
            <a href="{{ route('discuss.members') }}">
              <span class="glyphicon glyphicon-heart-empty"></span> 
              &nbsp;@lang('discuss.member-list.title')
            </a>
          </li>
          @if ($user)
          <li class="{{ active('dashboard') }}">
            <a href="{{ route('dashboard') }}">
              <span class="glyphicon glyphicon-dashboard"></span> 
              &nbsp;@lang('dashboard.title')
            </a>
          </li>
          <li>
            <a href="{{ route('logout') }}">
              <span class="glyphicon glyphicon-log-out"></span> 
              &nbsp;@lang('dashboard.logout')
            </a>
          </li>
          @else
          <li class="{{ active('login') }}">
            <a href="{{ route('login') }}">
              <span class="glyphicon glyphicon-log-in"></span> 
              &nbsp;@lang('dashboard.login')
            </a>
          </li>
          @endif
        </ul>
      </div><!--/.nav-collapse -->
    </div><!--/.container-fluid -->
  </div>

  <div class="container" id="site-container">
    <!-- scripting disabled warning -->
    <noscript>
      <div id="noscript" class="alert alert-danger">
        <strong><span class="glyphicon glyphicon-flag" aria-hidden="true"></span> @lang('home.noscript.title')</strong>
        <p>@lang('home.noscript.message', ['website' => config('ed.title')])</p>
        <p><a href="https://support.google.com/bin/answer.py?hl=en&amp;answer=23852" target="_blank">@lang('home.noscript.call-to-action')</a>.</p>
      </div>
    </noscript>

    <!-- search component -->
    <div id="ed-search-component"></div>

    <!-- begin content -->
    @yield('body')
    <div class="row">
      <p class="disclaimer">Black Speech, Nandorin, Noldorin, Quendya, Quenya, Sindarin, Telerin are languages conceived by Tolkien and they do not belong to us; we neither can nor do claim affiliation 
    with <a href="http://www.middleearth.com/" target="_blank">Middle-earth Enterprises</a> nor <a href="http://www.tolkienestate.com/" target="_blank">Tolkien Estate</a>.</p>
    </div>
  </div>

  <script type="text/javascript" src="@assetpath(/js/manifest.js)"></script>
  <script type="text/javascript" src="@assetpath(/js/vendor.js)"></script>
  <script type="text/javascript" src="@assetpath(/js/ie.js)"></script>
  <script type="text/javascript" src="@assetpath(/js/glaemscribe.js)"></script>
  @if ($user)
    @if ($isAdmin)
    <script type="text/javascript" src="@assetpath(/js/global-plugins-admin.js)"></script>
    @else
    <script type="text/javascript" src="@assetpath(/js/global-plugins-restricted.js)"></script>
    @endif
  @endif
  <script type="text/javascript" src="@assetpath(/js/global.js)" async></script>
  @yield('scripts')
  @if (!empty(config('ed.footer_view')))
    @include(config('ed.footer_view'))
  @endif
  </body>
</html>
