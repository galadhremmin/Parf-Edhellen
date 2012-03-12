<?php
  include_once 'lib/system.php';

  $template = 'index';
  if (isset($_GET['template']) && preg_match('/^[a-z]+$/', $_GET['template'])) {
    $template = $_GET['template'];
  }

  $r = new TemplateEngine();
  
  try {
    $r->displayEncapsulated($template);
  } catch (Exception $e) {
    $r->displayEncapsulated('error');
  }
?>