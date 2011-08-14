<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class TemplateEngine extends Smarty {
    public function __construct() {
      parent::__construct();
      $this->debugging = false;
      $this->cache_lifetime = 120;
    }
    
    public function __destruct() {
      parent::__destruct();
    }
    
    public function displayEncapsulated($file, $encapsulate = true, $cache = true) {
      $this->caching = $cache;    
    
      $controller = self::getController($file);
      
      if (!self::controllerExists($controller)) {
        throw new Exception('Controller '.$controller.' does not exist.');
      }
      
      $controller = new $controller($this);
    
      if ($encapsulate) {
        $this->displayEncapsulated('header', false, false);
      }
      
      parent::display($file.'.tpl');
      
      if ($encapsulate) {
        $this->displayEncapsulated('footer', false, true);
      }
    }
    
    private function getController($file) {
      return 'Page'.ucfirst($file).'Controller';
    }
    
    private function controllerExists($controller) {
      $file = ROOT.'lib/class/class.'.strtolower($controller).'.php'; 
      return file_exists($file);
    }
  }
?>