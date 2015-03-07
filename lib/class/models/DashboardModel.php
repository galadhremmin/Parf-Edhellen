<?php
  namespace models;
  
  class DashboardModel {
    private $_statistics;
    
    public function __construct() {
      $account =& \auth\Credentials::current()->account();
      
      $senseCount       = \data\entities\Sense::countByAccount($account);
      $translationCount = \data\entities\Translation::countByAccount($account);
    }
    
    public function getTerm() {
      return $this->_term;
    }
  }
?>
