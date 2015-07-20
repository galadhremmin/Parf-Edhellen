<?php
  namespace models;
  
  class AuthenticateModel {
    private $_providers;
    private $_message;
  
    public function __construct() {
      $providers = \data\entities\AuthProvider::getAllProviders();
      $this->_providers = array();
      
      foreach ($providers as $provider) {
        $this->_providers[$provider->id] = $provider;
      }

      $this->_message = '';
      if (isset($_GET['message'])) {
        try {
          $msg = base64_decode($_GET['message']);
          $this->_message = $msg;
        } catch (\Exception  $ex) {
          // Ignore
        }
      }
    }
    
    public function getProviders() {
      return $this->_providers;
    }

    public function getMessage() {
      return $this->_message;
    }
  }
