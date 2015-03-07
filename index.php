<?php
  include_once 'lib/system.php';

  $template = 'Index';
  if (isset($_GET['template']) && preg_match('/^[a-z]+[a-zA-Z]*$/', $_GET['template'])) {
    $template = ucfirst( $_GET['template'] );
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
?>
