<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageNewsModel {
    private $_activityList;
  
    public function __construct() {
      $db = Database::instance();
      
      $res = $db->connection()->query('
      SELECT k.`RelationID`, k.`Keyword`, a.`Nickname`, a.`AccountID`, IF(k.`CreationDate` = 0, t.`DateCreated`, k.`CreationDate`) AS `CreationDate`
      FROM `keywords` k
      LEFT JOIN `translation` t ON t.`TranslationID` = k.`TranslationID`
      LEFT JOIN `auth_accounts` a ON `a`.AccountID = t.`AuthorID`
      WHERE k.`NamespaceID` IS NULL
      ORDER BY k.`RelationID` DESC LIMIT 20');
      
      $this->_activityList = array();
      while ($obj = $res->fetch_object()) {
        $this->_activityList[] = $obj;
      }
      
      $res->free();
    }
    
    public function getActivityList() {
      return $this->_activityList;
    }
  }
?>
