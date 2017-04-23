<?php
  
  namespace data\entities;

  class Favourite extends Entity {

    public $accountID;
    public $id;
    public $translation;
    public $dateCreated;
    
    public static function getByAccount(Account &$account, $IDsOnly = false) {
      
      $db = \data\Database::instance()->connection();
      $query = null;
      $favourites = array();
      
      try {
        if ($IDsOnly) {
          $query = $db->prepare('SELECT `TranslationID` FROM `favourite` WHERE `AccountID` = ?');
          $query->bind_param('i', $account->id);
          $query->execute();
          $query->bind_result($id);
          
          while ($row = $query->fetch()) {
            $favourites[] = intval($id);
          }
          
        } else {
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
                'dateCreated' => \utils\ElfyDateTime::parse($dateCreated),
                'translation' => new Translation(
                    array('id' => $translationID, 'word' => $word)
                )
            ));
          }
        }
      } finally {
        $query = null;
      }
      
      return $favourites;
    }
    
    public function invalidate() {
      $this->accountID = 0;
      $this->dateCreated = null;
      $this->id = 0;
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
        $query = $db->prepare('SELECT `ID` FROM `favourite` WHERE `AccountID` =  ? AND `TranslationID` = ?');
        $query->bind_param('ii', $this->accountID, $this->translation->id);
        $query->execute();
        $query->bind_result($this->id);

        if ($query->fetch()) {
          $query->free_result();
          $query = null;

        } else {
          $query->free_result();
          $query = null;

          $query = $db->prepare('INSERT INTO `favourite` (`AccountID`, `TranslationID`, `DateCreated`) VALUES (?, ?, NOW())');
          $query->bind_param('ii', $this->accountID, $this->translation->id);
          $query->execute();
        }
      } finally {
        if ($query !== null) {
          $query->close();
        }
      }
    }

    public function loadForTranslation($id) {
      if (! is_numeric($id)) {
        return;
      }

      $credentials = \auth\Credentials::request(new \auth\BasicAccessRequest());
      $accountID = $credentials->account()->id;

      $db = \data\Database::instance()->connection();
      $query = $db->prepare('SELECT `ID` FROM `favourite` WHERE `TranslationID` = ? AND `AccountID` = ?');
      $query->bind_param('ii', $id, $accountID);
      $query->execute();
      $query->bind_result($this->id);

      if ($query->fetch()) {
        $query->free_result();
        $query = null;

        $this->load($this->id);
      }
    }
    
    public function load($id) {
      if (! is_numeric($id)) {
        return;
      }
      
      $db = \data\Database::instance()->connection();
      $query = null;
      
      try {
      
        $query = $db->prepare('SELECT f.`ID`, f.`AccountID`, f.`TranslationID`, f.`DateCreated`, w.`Key` FROM `favourite` f
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
          
          $this->dateCreated = \utils\ElfyDateTime::parse($dateCreated);
        }
        
      
      } finally {
        if ($query !== null) {
          $query->close();
        }
      }
    }
    
    public function remove() {
      if (!$this->validate()) {
        throw new \exceptions\ValidationException(__CLASS__);
      }
      
      $db = \data\Database::instance()->connection();
      $query = null;
      
      try {
                
        $query = $db->prepare('DELETE FROM `favourite` WHERE `AccountID` = ? AND `TranslationID` = ?');
        $query->bind_param('ii', $this->accountID, $this->translation->id);
        $query->execute();
        
        $this->invalidate();
        
      } finally {
        if ($query !== null) { 
          $query->close();
        }
      }
    }
  }