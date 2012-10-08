<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class Controller {
    private $_model;
    private $_timeElapsed;
  
    protected function __construct($controller, $cache = true, $customCacheTag = null) {
      // this is here only for first line in the identification of performance issues
      $startTime = microtime(true);
      
      // Acquire the class name for the model passed from the super class 
      $model = self::getModelClassName($controller);
      if (self::modelExists($model)) {
      
        if ($customCacheTag == null) {
          $customCacheTag = Caching::getDefaultTag().'.'.$model;
        }
      
        // very slow caching - there is no reason to continually refresh these unless
        // changes are made unless the client is logged in (where the client might see
        // things otherwise unavaiable)
        $c = new Caching(60 /* hourly */, $customCacheTag);
        
        if (!$cache || ($cache && (Session::isValid() || $c->hasExpired()))) {
          
          // Initiate an instance of the model class
          $this->_model = new $model();
          
          // Preserve the state if the client is not logged in as it is universal for
          // everyone.
          if ($cache && !Session::isValid()) {
            $c->save(serialize($this->_model));
          }
          
        } else {
          // Autoload the model to ensure that it is known to the PHP context as the 
          // serializer deserializes the cache data
          __autoload($model);
          
          // Deserialize the model
          $this->_model = unserialize($c->load());
        }
      }
      
      $this->_timeElapsed = microtime(true) - $startTime;
    }
    
    protected function getModel() {
      return $this->_model;
    }
    
    protected function getTimeElapsed() {
      return $this->_timeElapsed;
    }
    
    private function getModelClassName($controller) {
      return 'Page'.ucfirst($controller).'Model';
    }
    
    private function modelExists($model) {
      $file = ROOT.'lib/class/class.'.strtolower($model).'.php';
      return file_exists($file);
    }
  }
?>
