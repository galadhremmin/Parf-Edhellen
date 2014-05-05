<?php
  define('CONSOLIDATED_FILE_NAME', '../cache/_consolidate.js');
  define('CONSOLIDATION_LIFETIME', 60*60*24*7*30);

  include_once '../lib/system.php';

  $js = '';
  
  if (!file_exists(CONSOLIDATED_FILE_NAME) || time() - filemtime(CONSOLIDATED_FILE_NAME) > CONSOLIDATION_LIFETIME) {
    $files = array();
    $fileIt = new DirectoryIterator('.');
    foreach ($fileIt as $it) {
      if ($fileIt->getExtension() === 'js') {
        $files[] = $fileIt->getFilename();
      }
    }
    
    sort($files);
    
    foreach ($files as $file) {
      $js .= file_get_contents($file)."\n";
    }
    
    $js = JSMin::minify($js);
    file_put_contents(CONSOLIDATED_FILE_NAME, $js);
    
  } else {
    $js = file_get_contents(CONSOLIDATED_FILE_NAME);
  }
  
  $expires = 60*60*24*14;
  
  header("Content-Type: text/javascript; charset=utf-8");
  header("Pragma: public");
  header("Cache-Control: maxage=".$expires);
  header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

  echo $js;
