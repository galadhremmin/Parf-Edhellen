<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>{$documentTitle}</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <link rel="stylesheet" media="all" type="text/css" href="css/global.css" />
  <link rel="stylesheet" media="all" type="text/css" href="css/ui-lightness/jquery-ui-1.8.14.custom.css" />
  <script type="text/javascript" src="js/jquery.js"></script>
  <script type="text/javascript" src="js/jquery-ui-1.8.14.custom.min.js"></script>
  <script type="text/javascript" src="js/global.js"></script>
  <meta name="viewport" content="width=700;initial-scale=1.0Lmaximum-scale=1.0"/>
</head>
<body>
<h1>{$pageTitle}</h1>
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