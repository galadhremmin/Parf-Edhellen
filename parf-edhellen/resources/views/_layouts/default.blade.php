<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
  <title>@yield('title') - Parf Edhellen</title>
  <meta charset="UTF-8">
  <meta name="keywords" content="sindarin, quenya, noldorin, quendya, elvish, tolkien, nandorin, ilkorin, black speech, westron">
  <meta name="description" content="Parf Edhellen is one of the most comprehensive elvish dictionaries on the Internet, housing thousands of elvish names, words and phrases.">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link href="/css/app.css" rel="stylesheet">
  <noscript>
    <style type="text/css">
    .tengwar { display: none; }
    </style>
  </noscript>
</head>
<body>
  <div class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/">Parf Edhellen</a>
      </div>
      <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
          <li class="{{ active('') }}"><a href="/">Home</a></li>
          <li class="{{ active('phrases') }}"><a href="{{ route('phrases') }}">Phrases</a></li>
          <li class="{{ active('about') }}"><a href="{{ route('about') }}">About</a></li>
          <li class="{{ active('about.donations') }}"><a href="{{ route('about.donations') }}">Donations</a></li>
        </ul>
      </div><!--/.nav-collapse -->
    </div><!--/.container-fluid -->
  </div>

  <div class="container" id="site-container">
    <!-- scripting disabled warning -->
    <noscript>
      <div id="noscript" class="alert alert-danger">
        <strong><span class="glyphicon glyphicon-flag" aria-hidden="true"></span> Ai! Lá polin saca i quettar!</strong>
        <p><em>ElfDict</em> requires javascript to function properly. Please enable Javascript.</p>
        <p><a href="https://support.google.com/bin/answer.py?hl=en&amp;answer=23852" target="_blank">Show me how I enable Javascript</a>.</p>
      </div>
    </noscript>

    <!-- search component -->
    <div id="search-component"></div>

    <!-- begin content -->
    <div id="result">
      @yield('body')
    </div>
    <div class="row">
      <p class="disclaimer">Black Speech, Nandorin, Noldorin, Quendya, Quenya, Sindarin, Telerin are languages conceived by Tolkien and they do not belong to us; we neither can nor do claim affiliation 
    with <a href="http://www.middleearth.com/" target="_blank">Middle-earth Enterprises</a> nor <a href="http://www.tolkienestate.com/" target="_blank">Tolkien Estate</a>.</p>
    </div>
  </div>

  <script type="application/json" id="ed-preloaded-languages">{!! $allLanguages !!}</script>
  <script type="text/javascript" src="/js/global.js" async></script>
  <!--[if lte IE 8]>
  <script src="/js/compatibility/media-queries-ie8.js" type="text/javascript"></script>
  <![endif]-->
  </body>
</html>
