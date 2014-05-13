<?php
  define('CONSOLIDATION_LIFETIME', -1);

  include_once '../lib/system.php';

  $js = '';
  $cache = new Caching(CONSOLIDATION_LIFETIME, 'js');
  
  if ($cache->hasExpired()) {
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
    $js = '// '.date('Y-m-d H:i')."\n".$js;
    $cache->save($js);
    
    $expires = 60 * CONSOLIDATION_LIFETIME;
    
  } else {
    $js = $cache->load();
    $expires = $cache->getRemainingLifetime();
  }
  
  header("Content-Type: text/javascript; charset=utf-8");
  header("Pragma: public");
  header("Cache-Control: maxage=".$expires);
  header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');

  echo $js;
