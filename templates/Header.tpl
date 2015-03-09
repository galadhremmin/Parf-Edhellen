<!DOCTYPE html>
<html>
<head>
  <title>{$documentTitle}</title>
  <meta charset="UTF-8">
  <meta name="keywords" content="sindarin, quenya, noldorin, quendya, elvish, tolkien, nandorin, ilkorin, black speech, westron">
  <meta name="description" content="Parf Edhellen is one of the most comprehensive elvish dictionaries on the Internet, housing thousands of elvish names, words and phrases.">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/global.css" rel="stylesheet">
  <noscript>
    <style type="text/css">
    .tengwar { display: none; }
    </style>
  </noscript>
  {$additions}
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
        {foreach $menu as $item}
          {if $item->sectionIndex == 1}
          <li{if $item->active} class="active"{/if}><a href="{$item->url[0]}"{if $item->onclick != null} onclick="{$item->onclick}"{/if}>{$item->text}</a></li>
          {/if}
        {/foreach}
        </ul>
        <ul class="nav navbar-nav navbar-right">
        {foreach $menu as $item}
          {if $item->sectionIndex == 2}
          <li{if $item->active} class="active"{/if}><a href="{$item->url[0]}"{if $item->onclick != null} onclick="{$item->onclick}"{/if}>{$item->text}</a></li>
          {/if}
        {/foreach}
        </ul>
      </div><!--/.nav-collapse -->
    </div><!--/.container-fluid -->
  </div>

  <div class="container">
    <!-- scripting disabled warning -->
    <noscript>
      <div id="noscript" class="alert alert-danger">
        <strong><span class="glyphicon glyphicon-flag" aria-hidden="true"></span> Ai! Lá polin saca i quettar!</strong>
        <p><em>ElfDict</em> requires javascript to function properly. Please enable Javascript.</p>
        <p><a href="http://support.google.com/bin/answer.py?hl=en&amp;answer=23852" target="_blank">Show me how I enable Javascript</a>.</p>
      </div>
    </noscript>

    <!-- search component -->
    <form method="get" id="search-form" action="#">
      <div class="row">
        <div class="col-md-12">
          <div class="input-group input-group-lg">
            <span class="input-group-addon"><span class="glyphicon glyphicon-search" id="search-query-field-loading" data-loading-class="glyphicon glyphicon-refresh loading"></span></span>
            <input type="search" class="form-control" placeholder="your search query..." id="search-query-field" tabindex="1" accesskey="s" autocapitalize="off" autocorrect="off">
          </div>
        </div>
      </div>
      <div class="row" id="search-params-wrapper">
        <select id="search-language-select">
          {html_options options=$languages}
        </select>
        <div class="checkbox input-sm" id="search-reverse-box-wrapper">
          <label>
            <input type="checkbox" id="search-reverse-box" value="1"> Reverse search 
          </label>
        </div>
      </div>
    </form>

    <div id="search-result-wrapper" class="panel panel-default hidden">
      <div class="panel-heading">
        <h3 class="panel-title" id="search-result-wrapper-toggler-title"><span id="search-result-wrapper-toggler" class="glyphicon glyphicon-minus"></span> Matching words (<span id="search-result-count"></span>)</h3>
      </div>
      <div class="panel-body results-panel hidden">
        <div class="row">
          <div class="col-xs-12" id="search-result-description">
            These words match your search query. Click on the one most relevant to you, or simply press enter to expand the first item in the list.
          </div>
        </div>
        <div class="row">
          <div id="search-result"></div>
        </div>
      </div>
      <div class="panel-body results-empty hidden">
        <div class="row">
          <div class="col-xs-12"">
            Unfortunately, we were unable to find any words matching your search query. Have you tried a synonym, or perhaps even an antonym?
          </div>
        </div>
      </div>
    </div>
    
    <div class="row hidden" id="search-result-navigator">
      <nav>
        <ul class="pager">
          <li class="previous" id="search-result-navigator-backward"><a href="#"><span aria-hidden="true">&larr;</span> <span class="word">Previous word</span></a></li>
          <li class="next" id="search-result-navigator-forward"><a href="#"><span class="word">Next word</span> <span aria-hidden="true">&rarr;</span></a></li>
        </ul>
      </nav>
    </div>
    
    <!-- begin content -->
    <div id="result">
    
