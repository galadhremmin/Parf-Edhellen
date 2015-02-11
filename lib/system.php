<?php
  define('SYS_ACTIVE', true);

  include_once 'config/global.php';
  include_once 'lib/smarty/libs/Smarty.class.php';

  error_reporting(E_ALL | E_STRICT);
  mb_internal_encoding('UTF-8');
  
  class ClassPath {
    public $path;
    public $configPath;
    
    public function __construct($path, $configPath) {
      $this->path = $path;
      $this->configPath = $configPath;
    }
    
    public function exists() {
      return file_exists($this->path);
    }
  }
  
  class ClassInitializer {
    public static function resolvePath($className) {
      $pieces = explode('\\', $className);
      $lastIndex = count($pieces) - 1;
      $className = $pieces[$lastIndex];
      
      $pieces[$lastIndex] = $className.'.php';
      
      $file = ROOT.'lib/class/'.implode('/', $pieces);
      $config = ROOT.'lib/config/config.'.$className.'.php';
      
      return new ClassPath($file, $config);
    }
  }
  
  function __autoload($className) {

    if (substr($className, 0, 6) == 'Smarty') {
      smartyAutoload($className);
      return;
    }
    
    $paths = ClassInitializer::resolvePath($className);
    
    if (!$paths->exists()) {
      throw new Exception($className.' failed to load. Reason: '.$paths->path.' doesn\'t exist.');
    }
    
    if (file_exists($paths->configPath))
      include_once $paths->configPath;
    
    include_once $paths->path;
  }
  
  header('Content-Language: en');
