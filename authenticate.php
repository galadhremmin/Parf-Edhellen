<?php
  include_once 'lib/system.php';

  $r = new TemplateEngine();
  $r->display('authenticate', true, false);
?>