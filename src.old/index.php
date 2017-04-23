<?php
  include_once 'lib/system.php';

  $template = 'Index';
  if (isset($_GET['template']) && preg_match('/^[a-z\-]+$/', $_GET['template'])) {
    $parts = explode('-', $_GET['template']);
    $template = implode('', array_map('ucfirst', $parts));
  }

  $r = new TemplateEngine();
  
  try {
    $r->displayEncapsulated($template);
  } catch (\exceptions\InadequatePermissionsException $ex) {
    $r->displayEncapsulated('Error401');
  } catch (Exception $e) {
    if (DEBUG) {
      echo '<pre>'.$e.'</pre>';
    } else {
      $r->displayEncapsulated('Error500');
    }
  }
  