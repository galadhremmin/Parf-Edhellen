<?php
  namespace controllers;
  
  class Controller {
    private $_timeElapsed;
    
    protected $_model;
    protected $_engine;
  
    protected function __construct($controllerName, \TemplateEngine& $engine, $cache = true, $customCacheTag = null) {
      // this is here only for first line in the identification of performance issues
      $startTime = microtime(true);
      
      // Acquire the class name for the model passed from the super class 
      $model = self::getModelClassName($controllerName);
            
      if ($customCacheTag == null) {
        $customCacheTag = \data\Caching::getDefaultTag().'.'.$model;
      }
    
      if (\ClassInitializer::resolvePath($model)->exists()) {
        // Check if the user is logged in - cache is disabled for them
        $login = \auth\Credentials::permitted(new \auth\BasicAccessRequest());
        
        // very slow caching - there is no reason to continually refresh these unless
        // changes are made unless the client is logged in (where the client might see
        // things otherwise unavailable)
        $c = null;
        if ($cache && ! $login) { 
          $c = new \data\Caching(60 /* hourly */, $customCacheTag);
        } else {
          $cache = false;
        }
        
        if (! $cache || ($c !== null && $c->hasExpired())) {
          // Initiate an instance of the model class
          $this->_model = new $model();
          
          // Preserve the state if the client is not logged in as it is universal for
          // everyone.
          if ($c !== null) {
            $c->save(serialize($this->_model));
          }
          
        } else if ($cache) {
          // Autoload the model to ensure that it is known to the PHP context as the 
          // serializer deserializes the cache data
          __autoload($model);
          
          // Deserialize the model
          $this->_model = unserialize($c->load());
        }
      }
      
      $this->_engine = $engine;
      $this->_timeElapsed = microtime(true) - $startTime;
    }
    
    public function load() {
      // Noop by default
    }
    
    protected function getModel() {
      return $this->_model;
    }
    
    protected function getTimeElapsed() {
      return $this->_timeElapsed;
    }
    
    private function getModelClassName($controllerName) {
      return 'models\\'.ucfirst($controllerName).'Model';
    }
  }
