<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  abstract class ServiceBase {
    private $_methods;
   
    protected function __construct() {
      $this->_methods = array();
    }
  
    protected function registerMainMethod($method) {
      self::registerMethod('__main', $method);
    }
    
    protected function registerMethod($key, $method) {
      if (!method_exists($this, $method))
        throw new ErrorException(get_class($this).': method "'.$method.'" does not exist.');
      
      $this->_methods[$key] = $method;
    }
    
    public function handleParameterizedRequest(&$data, $param = null) {
      $methodName = $param;
      
      if (is_numeric($param)) {
        $methodName = '__main';
        $data = $param;
      }
      
      if (!isset($this->_methods[$methodName])) {
        throw new ErrorException("Unrecognised command '".$methodName."'.");
      }
      
      return call_user_func(array($this, $this->_methods[$methodName]), $data);
    }
    
    public function getContentHandler() {
      return new JSONHandler();
    }
    
    public abstract function handleRequest(&$data);
  }
?>