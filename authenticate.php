<?php
  include_once 'lib/system.php';

  $r = new TemplateEngine();
  $r->displayEncapsulated('authenticate', true, false);
?>