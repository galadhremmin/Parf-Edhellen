<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>{$documentTitle}</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="keywords" content="sindarin, quenya, noldorin, quendya, elvish, tolkien, nandorin, ilkorin, black speech, westron" />
  <meta name="description" content="Parf Edhellen is one of the most comprehensive elvish dictionaries on the Internet, housing thousands of elvish names, words and phrases." />
  <script type="text/javascript" src="js/js.php"></script>
  <link rel="stylesheet" media="all" type="text/css" href="css/global.css" />
  <link rel="stylesheet" media="all" type="text/css" href="css/ui-lightness/jquery-ui-1.8.14.custom.css" />
  <!--<meta name="viewport" content="width={$viewportWidth}, user-scalable=no, initial-scale=1"/>-->
  <style type="text/css">
  <!--/*<![CDATA[*/
  /*body { background-image: url(img/backgrounds/{$background}); }*/
  /*]]>*/-->
  </style>
  <script type="text/javascript">
  <!--//<![CDATA[
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-26836717-1']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    
    var ptr = LANGDict.contentLoaded;
    LANGDict.contentLoaded = function() {
      ptr.apply(LANGDict, arguments);
      _gaq.push(['_trackPageview',location.pathname + location.search  + location.hash]);
    }
  })();
  //]]>-->
  </script>
  <noscript>
    <style type="text/css">
    .tengwar { display: none; }
    </style>
  </noscript>
</head>
<body>
<!--{$pageTitle}-->
<h1>Parf Edhellen</h1>
<div id="menu">
  <ul>
    {foreach $menu as $item}
    <li><a href="{$item->url}"{if $item->onclick != null} onclick="{$item->onclick}"{/if}>{$item->text}</a></li>
    {/foreach}
  </ul>
</div>
<div id="contents">
  <div id="loading">
    Loading... please wait!
  </div>
  <noscript>
  <div id="noscript">
    <strong>Ai! Lá polin saca i quettar!</strong>
    <p><em>ElfDict</em> requires javascript to function properly. Please enable Javascript.</p>
    <p><a href="http://support.google.com/bin/answer.py?hl=en&amp;answer=23852" target="_blank">Show me how I enable Javascript</a>.</p>
  </div>
  </noscript>
  <!--
  <div id="noscript" style="background:green">
    <strong>Updating definitions from Ardalambion!</strong>
    <p>Parma Eldaliéva is presently being updated with the latest definitions from Ardalambion's Quenya Wordlist. Please bear with us as this process is undertaken.</p>
  </div>
  -->
  <div id="search-container">
   <div id="search-pane">
     <form method="get" id="search-form" action="#" onsubmit="return LANGDict.submit()">
      <h2>Search term</h2>
      <input id="search-query-field" type="search" size="34" class="rounded word" tabindex="1" accesskey="s" autocapitalize="off" autocorrect="off" />

      <div id="search-result"></div>
      <select name="search-filter" id="search-filter-field">
        {html_options options=$languages}
      </select>
      <div id="search-description">
        <p>Showing <span></span> results of <span></span>. <a href="about.page?browseTo=search">What is this?</a> This query took <span></span> ms.</p>
<!--        Select the first search result with the tab key and navigate using the arrow keys. While browsing the results, press <em>S</em> to return to the search field. 
        Use page up and page down to scroll between the translation entries.-->
     </div>

    </form>
  </div>
 </div>
 <div id="result" class="google-translate">
