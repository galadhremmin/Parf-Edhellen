<?php
  namespace models;
  
  class AuthenticateModel {
    private $_providers;
  
    public function __construct() {
      $providers = \data\entities\AuthProvider::getAllProviders();
      $this->_providers = array();
      
      foreach ($providers as $provider) {
        $this->_providers[$provider->id] = $provider;
      }
    }
    
    public function getProviders() {
      return $this->_providers;
    }
  }
