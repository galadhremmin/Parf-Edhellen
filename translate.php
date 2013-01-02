<?php
  include_once 'lib/system.php';
  
  $encapsulate = false;
  $cache = false;
  
  if (isset($_REQUEST['ajax']) && strtoupper($_REQUEST['ajax']) === 'FALSE') {
    $encapsulate = true;
    $cache = false;
  }
  
  $r = new TemplateEngine();
  $r->displayEncapsulated('translate', $encapsulate, $cache);
?>