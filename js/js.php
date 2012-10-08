<?php
  $filter = null;
  if (isset($_GET['filter']) && preg_match('/^[a-z0-9\\-]+$/i', $_GET['filter'])) {
    $filter = str_replace('-', '\\-', $_GET['filter']);
  }
  
  $files = scandir('.');
  sort($files);
  
  $js = '';
  foreach ($files as $file) {
    if ($file == '.' || $file == '..')
      continue;
    
    $info = pathinfo($file);
    
    if ($info['extension'] == 'js') {
      if ($filter == null || ($filter != null && preg_match('/^[0-9]+\\.'.$filter.'$/', $info['filename']))) {
        $js .= file_get_contents($file)."\n\n";
      }
    }
  }
  
  $expires = 60*60*24*14;
  
  header("Content-Type: text/javascript; charset=utf-8");
  header("Content-Length: ".strlen($js));
  header("Pragma: public");
  header("Cache-Control: maxage=".$expires);
  header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

  echo $js;
?>