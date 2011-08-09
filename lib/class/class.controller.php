<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class Controller {
    protected $_model;
  
    protected function __construct($controller) {
      $model = self::getModel($controller);
      if (self::modelExists($model)) {
        $this->_model = new $model();
      }
    }
    
    private function getModel($controller) {
      return 'Page'.ucfirst($controller).'Model';
    }
    
    private function modelExists($model) {
      $file = 'lib/class/class.'.strtolower($model).'.php';
      return file_exists($file);
    }
  }
?>