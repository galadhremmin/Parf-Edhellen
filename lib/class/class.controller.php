<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class Controller {
    protected $_model;
  
    protected function __construct($controller) {
      $model = self::getModel($controller);
      if (self::modelExists($model)) {
        $c = new Caching(30, Caching::getDefaultTag().'.'.$model);
        
        if (Session::isValid() || $c->hasExpired()) {
          $this->_model = new $model();
          
          if (!Session::isValid()) {
            $c->save(serialize($this->_model));
          }
          
        } else {
          __autoload($model);
          $this->_model = unserialize($c->load());
        }
        
      }
    }
    
    private function getModel($controller) {
      return 'Page'.ucfirst($controller).'Model';
    }
    
    private function modelExists($model) {
      $file = ROOT.'lib/class/class.'.strtolower($model).'.php';
      return file_exists($file);
    }
  }
?>