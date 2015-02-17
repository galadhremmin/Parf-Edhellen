<?php
  namespace models;
  
  class WrapperModel {
    private $_additions;
    
    protected function __construct($constantName) {
      $this->_additions = null;
      
      if (defined($constantName)) {
        $file = constant($constantName);
        
        if ($file !== null && !empty($file) && file_exists($file)) {
          $this->_additions = file_get_contents($file);
        }
      }
    }
    
    public function getAdditions() {
      return $this->_additions;
    }
  }
