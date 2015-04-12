<?php
  namespace models;
  use \data\entities;
  
  class DashboardModel {
    private $_statistics;
    
    public function __construct() {
      $account =& \auth\Credentials::current()->account();
      
      $this->_translations = entities\Translation::getByAccount($account);
    }
    
    public function getTranslations() {
      return $this->_translations;
    } 
  }
?>
