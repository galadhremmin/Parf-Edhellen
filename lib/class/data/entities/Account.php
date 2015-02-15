<?php
  namespace data\entities;
  
  class Account extends Entity {
    
    public $identity;
    public $id;
    public $nickname;
    public $tengwar;
    public $profile;
    public $configured;
    public $groups;
    
    public function __construct($data = null) {
      parent::__construct($data);
      
      $this->id = 0;
      $this->nickname = null;
      $this->tengwar = null;
      $this->profile = null;
      $this->configured = 0;
      $this->groups = array();
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
      } else {
        $this->saveChanges();
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
    
    private function saveChanges() {
      \auth\Session::canWriteSelf();
    
      $db      = \data\Database::instance()->connection();
      $account = \auth\Session::getAccount();
      
      if (!is_numeric($account->id)) {
        return;
      }
      
      // it's unfortunately necessary to use the query function here, because mysqli::fetch_assoc() isn't supported by earlier versions of mysqli.
      $result = $db->query('SELECT `Nickname`, `Configured`, `Tengwar`, `Profile` FROM `auth_accounts` WHERE `AccountID` = '.$account->id);
      $values = $result->fetch_assoc();
      $result->close();
      
      // Analyze current values and find discrepancies.
      $discrepancies = array();
      foreach ($values as $key => $existingValue) {
        $field = strtolower($key);
        $value = $this->$field;
        
        if ($value !== null) {
          $discrepancies[$key] = $value;
        }
      }
      
      // build a SQL query which will save the changes (disrepancies) to the database. It'll require a bit of a hack, because
      // mysqli doesn't support arrays in bind_param.
      if (count($discrepancies)) {
      
        // If nickname has changed, make sure it's still unique
        if (!$this->isNicknameUnique()) {
          throw new \ErrorException($this->nickname.' already exists.');
        }
      
        $query = $db->prepare(
          'UPDATE `auth_accounts` SET `'.implode('` = ?, `', array_keys($discrepancies)).'` = ? WHERE AccountID = ?'
        );
        
        // use call_user_func_array, as $query->bind_param('s', $param); does not accept params array
        $params = array(str_pad('i', count($discrepancies) + 1, 's', STR_PAD_LEFT));    
           
        foreach ($discrepancies as $key => $value) {
          $params[] = & $discrepancies[$key]; // call_user_func requires references, so...
        }
        $params[] = & $account->id;
        
        call_user_func_array(array($query, 'bind_param'), $params);
        
        $query->execute();
        $query->close();
      }
    }
    
    private function isNicknameUnique() {
      $db = \data\Database::instance()->connection();
      
      $query = $db->prepare('SELECT COUNT(*) FROM `auth_accounts` WHERE `Nickname` = ? AND `AccountID` <> ?');
      $query->bind_param('si', $this->nickname, $this->id);
      $query->execute();
      $query->bind_result($count);
      $query->fetch();
      $query->close();
      
      return $count < 1;
    }
  }
