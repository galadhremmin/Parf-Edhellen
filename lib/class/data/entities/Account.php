<?php
  namespace data\entities;
  
  class Account extends Entity {
    
    public $identity;
    public $id;
    public $nickname;
    public $configured;
    public $groups;
    
    public function __construct($data = null) {
      parent::__construct($data);
    }
    
    public function validate() {
      if ($this->id === 0) {
        return false;
      }
      
      return true;
    }
    
    public function load($salted_identity, $completeLoad = false) {
      if ($completeLoad) {
        throw new \exceptions\NotImplementedException('Complete loading mode in '.__METHOD__);
      }
    
      $db = \data\Database::instance();
      
      $query = $db->connection()->prepare(
        'SELECT `AccountID`, `Nickname`, `Configured` FROM `auth_accounts` WHERE `Identity` = ?'
      );
      
      $query->bind_param('s', $salted_identity);
      $query->execute();
      $query->bind_result($this->id, $this->nickname, $this->configured);
      $query->fetch();
      
      // ensure that the bit is converted to a boolean value
      $this->configured = $this->configured == 1;
      
      $query->close();
      
      if (!$this->validate()) {
        return;
      }
      
      $query = $db->connection()->prepare(
        'SELECT g.`name` FROM `auth_accounts_groups` rel
           INNER JOIN `auth_groups` g ON g.`ID` = rel.`GroupID`
         WHERE rel.`AccountID` = ?'
      );
      
      $query->bind_param('i', $this->id);
      $query->execute();
      $query->bind_result($groupName);
      
      $this->groups = array();
      while ($query->fetch()) {
        $this->groups[] = $groupName;
      }
      
      $query->close();
    }
    
    public function save() {
      
      if ($this->id == 0 && !empty($this->identity)) {
        $this->create();
      }
      
    }

    private function create() {
      $db = \data\Database::instance();
      
      if (empty($this->nickname)) {
        $this->nickname = null;
      }
      
      $query = $db->connection()->prepare(
        "INSERT INTO `auth_accounts` (`Identity`, `Nickname`, `DateRegistered`, `Configured`) VALUES (?, ?, NOW(), '0')"
      );
      $query->bind_param('ss', $this->identity, $this->nickname);
      $query->execute();
      $this->id = $query->insert_id;
      $query->close();
      
      $query = $db->connection()->prepare(
        'INSERT INTO `auth_accounts_groups` (`AccountID`, `GroupID`) 
         SELECT ?, GroupID FROM `auth_groups` WHERE `name` = \'User\' LIMIT 1' // limit 1 should be unnecessary, but just in case hic sunt dracones...
      );
      $query->bind_param('i', $this->id);
      $query->execute();
      $query->close();
    }
  }
