<!DOCTYPE html>
<html>
<head>
  <title>{$documentTitle}</title>
  <meta charset="UTF-8">
  <meta name="keywords" content="sindarin, quenya, noldorin, quendya, elvish, tolkien, nandorin, ilkorin, black speech, westron">
  <meta name="description" content="Parf Edhellen is one of the most comprehensive elvish dictionaries on the Internet, housing thousands of elvish names, words and phrases.">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/global.css" rel="stylesheet">
  

<!--

  <script type="text/javascript" src="js/js.php"></script>

  <link rel="stylesheet" media="all" type="text/css" href="css/global.css" />
  <link rel="stylesheet" media="all" type="text/css" href="css/ui-lightness/jquery-ui-1.8.14.custom.css" />  
-->
  
  <style type="text/css">
  <!--/*<![CDATA[*/
  /*body { background-image: url(img/backgrounds/{$background}); }*/
  /*]]>*/-->
  </style>
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
        {foreach $menu as $item}
          {if $item->sectionIndex == 1}
          <li{if $item->active} class="active"{/if}><a href="{$item->url}"{if $item->onclick != null} onclick="{$item->onclick}"{/if}>{$item->text}</a></li>
          {/if}
        {/foreach}
        </ul>
        <ul class="nav navbar-nav navbar-right">
        {foreach $menu as $item}
          {if $item->sectionIndex == 2}
          <li><a href="{$item->url}"{if $item->active} class="active"{/if}{if $item->onclick != null} onclick="{$item->onclick}"{/if}>{$item->text}</a></li>
          {/if}
        {/foreach}
          <li><a href="#top"><span class="glyphicon glyphicon-chevron-up"></span> To top</a></li>
        </ul>
      </div><!--/.nav-collapse -->
    </div><!--/.container-fluid -->
  </div>

  <div class="container">
    <!-- scripting disabled warning -->
    <noscript>
      <div id="noscript">
        <strong>Ai! Lá polin saca i quettar!</strong>
        <p><em>ElfDict</em> requires javascript to function properly. Please enable Javascript.</p>
        <p><a href="http://support.google.com/bin/answer.py?hl=en&amp;answer=23852" target="_blank">Show me how I enable Javascript</a>.</p>
      </div>
    </noscript>

    <!-- search component -->
    <form method="get" id="search-form" action="#" onsubmit="return LANGDict.submit()">
      <div class="row">
        <div class="input-group input-group-lg">
          <span class="input-group-addon"><span class="glyphicon glyphicon-search"></span></span>
          <input type="search" class="form-control" placeholder="your search term..." id="search-query-field" tabindex="1" accesskey="s" autocapitalize="off" autocorrect="off">
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="checkbox input-lg pull-right">
            <label>
              <input type="checkbox"> Reverse search 
            </label>
          </div>
        </div>
      </div>
    </form>

    <div id="search-result-wrapper" class="panel panel-default hidden">
      <div class="panel-heading">
        <h3 class="panel-title" id="search-result-wrapper-toggler-title"><span id="search-result-wrapper-toggler" class="glyphicon glyphicon-minus"></span> Suggestions (<span id="search-result-count"></span>)</h3>
      </div>
      <div class="panel-body">
        <div class="row">
          <div id="search-result"></div>
        </div>
      </div>
    </div>

    <!-- begin content -->
    <div id="result">
    
