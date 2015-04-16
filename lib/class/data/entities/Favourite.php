<?php
  
  namespace data\entities;

  class Favourite extends Entity {

    public $accountID;
    public $id;
    public $translation;
    public $dateCreated;
    
    public static function getByAccount(Account &$account) {
      
      $db = \data\Database::instance()->connection();
      $query = null;
      $favourites = array();
      
      try {
        $query = $db->prepare('SELECT f.`ID`, w.`Key`, t.`TranslationID`, f.`DateCreated` FROM `favourite` f 
            INNER JOIN `translation` t ON t.`TranslationID` = f.`TranslationID`
            INNER JOIN `word` w ON w.`KeyID` = t.`WordID` 
            WHERE f.`AccountID` = ?
            ORDER BY w.`Key` ASC');
        $query->bind_param('i', $account->id);
        $query->execute();
        $query->bind_result($id, $word, $translationID, $dateCreated);
        
        while ($row = $query->fetch()) {
          $favourites[] = new Favourite(array(
              'accountID'   => $account->id,
              'id'          => $id,
              'dateCreated' => new \DateTime($dateCreated),
              'translation' => new Translation(
                  array('id' => $translationID, 'word' => $word)
              )
          ));
        }
      } finally {
        if ($query !== null) {
          $query->close();
        }
      }
      
      return $favourites;
    }
    
    public function validate() {
      if ($this->accountID == 0) {
        return false;
      }
      
      if ($this->translation instanceof Translation == false) {
        return false;
      }
      
      if ($this->translation->id === 0) {
        return false;
      }
      
      return true;
    }
    
    public function save() {
      if (!$this->validate()) {
        throw new \exceptions\ValidationException(__CLASS__);
      }
      
      $db = \data\Database::instance()->connection();
      $query = null;
      
      try {
        
        $query = $db->prepare('REPLACE INTO `favourite` (`AccountID`, `TranslationID`, `DateCreated`) VALUES (?, ?, NOW())');
        $query->bind_param('ii', $this->accountID, $this->translation->id);
        $query->execute();
        
      } finally {
        if ($query !== null) {
          $query->close();
        }
      }
    }
    
    public function load($id) {
      if (!is_numeric($id)) {
        return;
      }
      
      $db = \data\Database::instance()->connection();
      $query = null;
      
      try {
      
        $query = $db->prepare('SELECT f.`ID` f.`AccountID`, f.`TranslationID`, f.`DateCreated`, w.`Key` FROM `favourite` f
            INNER JOIN `translation` t ON t.`TranslationID` = f.`TranslationID` 
            INNER JOIN `word` w ON w.`KeyID` = t.`WordID`
            WHERE f.`ID` = ?');
        $query->bind_param('i', $id);
        $query->execute();
        $query->bind_result($this->id, $this->accountID, $translationID, $dateCreated, $word);
        
        if ($query->fetch()) {
          $this->translation = new Translation(array(
              'id'   => $translationID,
              'word' => $word
          ));
          
          $this->dateCreated = new \DateTime($dateCreated);
        }
        
      
      } finally {
        if ($query !== null) {
          $query->close();
        }
      }
    }
  }