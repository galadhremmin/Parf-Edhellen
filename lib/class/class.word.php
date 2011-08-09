<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }

  class Word extends Entity {
    public $id;
    public $key;
    public $authorID;
  
    public function __construct($data = null) {
      if ($data !== null && is_array($data)) {
        $fields = get_object_vars($this);
      
        foreach ($fields as $field => $type) {
          if (isset($data[$field])) {
            $value = $data[$field];
          
            if (!is_numeric($value) && !is_null($value))
              $value = mb_strtolower($value, 'UTF-8');
          
            $this->$field = $value;
          }
        }
      }
    }
    
    public function create($key) {
      $db = Database::instance()->exclusiveConnection();
      
      $query = $db->prepare(
        'SELECT `KeyID`, `AuthorID` FROM `word` WHERE `Key` = ?'
      );
      
      $query->bind_param('s', $key);
      $query->execute();
      $query->bind_result($this->id, $this->authorID);
      
      if ($query->fetch() !== true) {
        $query->close();
        
        $this->authorID = Session::getAccountID();
        
        if ($this->authorID < 1) {
          throw new ErrorException('Invalid or malformed session cookie.');
        }
        
        $query = $db->prepare(
          'INSERT INTO `word` (`Key`, `AuthorID`) VALUES (?, ?)'
        );
        
        $query->bind_param('si', $key, $this->authorID);
        if (!$query->execute()) {
          throw new ErrorException('Word insertion failed.');
        }
        
        $this->id = $db->insert_id;
      }
      
      $query->close();
      
      $this->key = $key;
      
      return $this;
    }
    
    public function load($id) {
      $db = Database::instance();
      
      $query = $db->connection()->prepare(
        'SELECT `Key`, `AuthorID` 
         FROM `word` WHERE `KeyID` = ?'
      );
      $query->bind_param('i', $id);
      $query->execute();
      $query->bind_result($this->key, $this->authorID);
      
      if ($query->fetch() !== true) {
        throw new Exception('Word id '.$id.' does not exist.');
      }
      $query->close();
      
      $this->id = $id;
      
      return $this;
    }
    
    public function validate() {
      return $this->key != null && $this->keyID != null &&
             !preg_match('/^\\s*$/', $this->key) && 
             !preg_match('/^\\s*$/', $this->keyID);
    }
    
    public function save() {
      if ($this->id) {
        throw new ErrorException('Word has already been assigned an ID and consequently exists already.');
      }
      
      if (!$this->validate()) {
        throw new ErrorException('Invalid or malformed Word-object.');
      }
      
      // exclusive connections require the current account to be authenticated 
      $db = Database::instance()->exclusiveConnection();
      
      $query = $db->prepare(
        'SELECT `KeyID` FROM `word` WHERE `Key` = ?'
      );
      
      $query->bind_param('s', $this->key);
      $query->execute();
      $query->bind_result($this->id);
      
      // doesn't exist
      if ($query->fetch() !== true) {
        $query->close();
        
        $query = $db->prepare(
          'INSERT INTO `word` (`Key`, `AuthorID`) VALUES (?, ?)'
        );
        
        $query->bind_param('si', $this->key, Session::getAccountID());
        $query->execute();
        
        $this->id = $query->insert_id;
      } else {
        $this->load($this->id);
      }
      
      $query->close();
      
      return $this;
    }
    
    public static function unregisterReference($id, $threshold = 1) {
      $db = Database::instance()->exclusiveConnection();
      
      $query = $db->prepare('SELECT COUNT(t.`TranslationID`) 
                             FROM `translation` t
                             LEFT JOIN `inflection` i ON i.`TranslationID` = t.`TranslationID` 
                             WHERE t.`Latest` = 1 AND (i.`WordID` = ? OR t.`WordID` = ?)');
      $query->bind_param('i', $id, $id);
      $query->execute();
      $query->bind_result($count);
      
      if ($query->fetch() && $count < $threshold) {
        $query->close();
        
        $query = $db->prepare('DELETE FROM `word` WHERE `KeyID` = ?');
        $query->bind_param('i', $id);
        $query->execute();
      }
      
      $query->close();
    }
    
    public static function registerIndex(Translation& $trans) {
      $trans->translation = 'index';
      
      if ($trans->word === null) {
        throw new ErrorException('Index missing key.');
      }
      
      $word = new Word();
      $word->create($trans->word);
      
      $db = Database::instance();
      $query = $db->connection()->prepare(
        'SELECT `TranslationID` FROM `translation` WHERE `WordID` = ? AND `NamespaceID` = ?'
      );
      $query->bind_param('ii', $word->id, $trans->namespaceID);
      $query->execute();
      $query->bind_result($id);
      
      if ($query->fetch()) {
        $trans->id = $id;
      } else {
        $trans = self::register($trans, $word);
      }
      
      $query->close();
      
      return $trans;
    }
    
    public static function registerTranslation(Translation& $trans) {
      if ($trans === null) {
        throw new ErrorException('Null pointer exception for Translation');
      }

      return self::register($trans);
    }
    
    private function register(Translation& $trans, Word $word = null) {
      if (!$trans->validate()) {
        throw new ErrorException('Invalid translation.');
      }
      
      if ($word === null) {
        $word = new Word();
        $word->create($trans->word);
      }
    
      // Acquire a connection for making changes in the database.
      $db = Database::instance()->exclusiveConnection();

      // Deprecate current translation entry
      if ($trans->id > 0) {
        // Indexes doesn't use words, hence this functionality applies only
        // to translations.
        $query = $db->prepare('SELECT `WordID` FROM `translation` WHERE `TranslationID` = ?');
        $query->bind_param('i', $trans->id);
        $query->execute();
        $query->bind_result($currentWordID);
        $query->fetch();
      
        if ($currentWordID != $word->id) {
          Word::unregisterReference($currentWordID, 2); // 2 because the entry has not yet been changed
        }
        
        $query->close();
      
        $query = $db->prepare("UPDATE `translation` SET `Latest` = '0' WHERE `TranslationID` = ?");
        $query->bind_param('i', $trans->id);
        $query->execute();
        $query->close();
      }
      
      // Acquire current author
      $accountID = Session::getAccountID();
      
      if ($accountID < 1) {
        throw new ErrorException('Invalid log in state.');
      }
      
      // Insert the row
      $query = $db->prepare(
        "INSERT INTO `translation` (`Translation`, `Etymology`, `Type`, `Source`, `Comments`, 
        `Tengwar`, `Phonetic`, `LanguageID`, `WordID`, `NamespaceID`, `AuthorID`, `Latest`, 
        `DateCreated`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '1', NOW())"
      );
      $query->bind_param('sssssssiiii',
        $trans->translation, $trans->etymology, $trans->type, $trans->source, $trans->comments,
        $trans->tengwar, $trans->phonetic, $trans->language, $word->id, $trans->namespaceID,
        $accountID
      );
      $query->execute();
      
      $trans->id = $query->insert_id;
      
      $query->close();
      
      // if word is null, index, else translation.
      return $word === null ? $trans : $word;
    }
  }
?>