<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageAuthenticateModel {
    private $_providers;
  
    public function __construct() {
      $db = Database::instance();
      
      $query = $db->connection()->query(
        'SELECT `ProviderID`, `Name`, `Logo`, `URL` FROM `auth_providers` ORDER BY `Name` ASC'
      );
      
      $this->_providers = array();
      while ($row = $query->fetch_object()) {
        $this->_providers[$row->ProviderID] = $row;
      }
      
      $query->close();
    }
    
    public function getProviders() {
      return $this->_providers;
    }
  }
?>