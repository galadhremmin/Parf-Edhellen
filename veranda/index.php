<?php
  include_once '../lib/system.php';

  $template = 'xp_index';
  if (isset($_GET['template']) && preg_match('/^[a-z]+$/', $_GET['template'])) {
    $template = $_GET['template'];
  }

  $r = new TemplateEngine('xp_header', 'xp_footer');

  try {
    $r->displayEncapsulated($template);
  } catch (Exception $e) {
    $r->displayEncapsulated('error');
  }
?>