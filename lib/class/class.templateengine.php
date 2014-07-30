<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class TemplateEngine extends Smarty {
    private $_headerName;
    private $_footerName;
  
    public function __construct($headerName = 'header', $footerName = 'footer') {
      parent::__construct();
      
      // SMARTY-specific information
      $this->debugging = false;
      $this->cache_lifetime = 120;
      
      // encapsulation
      $this->_headerName = $headerName;
      $this->_footerName = $footerName;
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
      
      if (isset($_POST['action'])) {
        $action = preg_replace('/[^0-9a-zA-Z]+/', '', $_POST['action']).'Action';
        
        if (method_exists($controller, $action)) {
          $arguments = array();
          foreach ($_POST as $key => $value) {
            if ($key === 'action') {
              continue;
            }
            
            $arguments[$key] = $value;
          }
          
          $controller->$action($arguments);
        } else {
          throw new Exception('Unrecognised action "'.$action.'".');
        }
      }
      
      $controller->load();
    
      if ($encapsulate) {
        $this->displayEncapsulated($this->_headerName, false, false);
      }
      
      parent::display(ROOT.'/templates/'.$file.'.tpl');
      
      if ($encapsulate) {
        $this->displayEncapsulated($this->_footerName, false, true);
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
