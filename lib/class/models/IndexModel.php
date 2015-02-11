<?php
  namespace models;
  
  class IndexModel {
    private $_languages;
    public function __construct() {
      /*
      $db = Database::instance();
      
      $res = $db->connection()->query(
        "SELECT 
          l1.`ID` AS `FromID`, l1.`Name` AS `FromName`, 
          l2.`ID` AS `ToID`, l2.`Name` AS `ToName` 
          FROM `language` l1 
          CROSS JOIN `language` l2 
          WHERE l1.`ID` <> l2.`ID` 
          ORDER BY l1.`Name` DESC"
      );
      
      $this->_languages = array();
      while ($row = $res->fetch_object()) {
        $this->_languages[$row->FromID.':'.$row->ToID] = $row->FromName.' to '.$row->ToName;
      }
      
      $res->close();
      */
    }
    
    public function getLanguages() {
      return $this->_languages;
    }
  }
