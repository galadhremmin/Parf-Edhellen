<?php
  namespace data\entities;

  class Author extends Entity {
    public $id;
    public $nickname;
    public $tengwar;
    public $profile;
    public $dateRegistered;
    public $isConfigured;
    
    // non-essential statistics
    public $translationCount;
    public $wordCount;
  
    public function __construct($data = null) {
      parent::__construct($data);
    }
    
    public function validate() {
      return $this->id < 1 || !preg_match('/^\\s*$/', $this->nickname);
    }
    
    public function load($id) {
      $db = \data\Database::instance();
      $query = $db->connection()->prepare(
        'SELECT a.`AccountID`, a.`Nickname`, a.`DateRegistered`, a.`Configured`, a.`Tengwar`, a.`Profile`,
         (SELECT COUNT(`TranslationID`) FROM `translation` WHERE `AuthorID` = a.`AccountID`) 
          AS `Translations`,
         (SELECT COUNT(`KeyID`) FROM `word` WHERE `AuthorID` = a.`AccountID`) 
          AS `Words`
         FROM `auth_accounts` a
         WHERE a.`AccountID` = ?'
      );
      $query->bind_param('i', $id);
      $query->execute();
      $query->bind_result(
        $this->id, $this->nickname, $this->dateRegistered, 
        $this->isConfigured, $this->tengwar, $this->profile,
        $this->translationCount, $this->wordCount
      );
      
      if ($query->fetch () !== true) {
        throw new Exception('Account '.$id.' does not exist.');
      }
      
      return $this;
    }
    
    public function save() {
      if (!$this->validate()) {
        throw new \ErrorException('Invalid or insufficient parameters.');
      }
      
      $db = \data\Database::instance()->connection();
      
      $query = $db->prepare(
        'UPDATE `auth_accounts` SET `Nickname` = ?, `Tengwar` = ?, `Profile` = ? WHERE `AccountID` = ?'
      );
      
      $query->bind_param('sssi', $this->nickname, $this->tengwar, $this->profile, $this->id);
      $query->execute();
      $query->close();
      
      return $this;
    }
    
    public function complete() {
      if (! $account->configured) {
        $account->configured = true;
        $account->save();
      }
    }
  }
  
