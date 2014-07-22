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
    
    public function load($id) {
      $db = Database::instance();
      
      $query = $db->connection()->prepare(
        'SELECT `Key`, `AuthorID` FROM `word` WHERE `KeyID` = ?'
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
      return $this->key != null && !preg_match('/^\\s*$/', $this->key) && 
             ($this->id === null || is_numeric($this->id));
    }
    
    public function create($key) {
      $this->id = 0;
      $this->key = $key;
      
      return $this->save();
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
          'INSERT INTO `word` (`Key`, `NormalizedKey`, `ReversedNormalizedKey`, `AuthorID`) VALUES (?, ?, ?, ?)'
        );
        
        $normalizedKey = StringWizard::normalize($this->key);
        $reversedNormalizedKey = strrev($normalizedKey);
        $accountID = Session::getAccountID();
        $query->bind_param('sssi', $this->key, $normalizedKey, $reversedNormalizedKey, $accountID);
        $query->execute();
        
        $this->id = $query->insert_id;
        
        $query->close();
      } else {
        $query->close();
        
        $this->load($this->id);
      }
      
      return $this;
    }
    
    public static function unregisterReference($id, $threshold = 1) {
      $db = Database::instance()->exclusiveConnection();
      
      $query = $db->prepare('SELECT COUNT(*) FROM `keywords` k WHERE k.`WordID` = ?');
      $query->bind_param('i', $id);
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
      $trans->index = true;
      
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
      
      $trans->index = false;

      return self::register($trans);
    }
    
    private static function register(Translation& $trans, Word $word = null) {
      if (!$trans->validate()) {
        throw new InvalidParameterException('translation');
      }
      
      if ($word === null) {
        $word = new Word();
        $word->create($trans->word);
      }
    
      // Acquire a connection for making changes in the database.
      $db = Database::instance()->exclusiveConnection();
      
      // check namespace validity
      $namespace = new DictionaryNamespace();
      if ($namespace->load($trans->namespaceID) === null) {
        throw new InvalidParameterException('namespaceID');
      }
      
      // Acquire current author
      $accountID = Session::getAccountID();

      // Deprecate current translation entry
      if ($trans->id > 0) {
        // Indexes doesn't use words, hence this functionality applies only
        // to translations.
        $query = $db->prepare('SELECT `WordID`, `NamespaceID` FROM `translation` WHERE `TranslationID` = ? AND (`EnforcedOwner` = 0 OR `EnforcedOwner` = ?)');
        $query->bind_param('ii', $trans->id, $accountID);
        $query->execute();
        $query->bind_result($currentWordID, $currentNamespaceID);
        $query->fetch();
        $query->close();
      
        $query = $db->prepare('UPDATE `translation` SET `Latest` = \'0\' WHERE `TranslationID` = ?');
        $query->bind_param('i', $trans->id);
        $query->execute();
        $query->close();
        
        // remove all keywords to the (now) deprecated translation entry - the keywords table
        // shall only contain current, up-to-date definitions.
        $query = $db->prepare('DELETE FROM `keywords` WHERE `TranslationID` = ?');
        $query->bind_param('i', $trans->id);
        $query->execute();
        $query->close();
        
        // deassociate the word with the previous translation entry
        if ($currentWordID != $word->id) {
          Word::unregisterReference($currentWordID);
        }
        
        // deassociate the namespace with the previous translation entry
        if ($currentNamespaceID != $trans->namespaceID) {
          $query = $db->prepare('SELECT COUNT(*) FROM `translation` WHERE `Latest` = 1 AND `NamespaceID` = ?');
          $query->bind_param('i', $currentNamespaceID);
          $query->execute();
          $query->bind_result($references);
          $query->fetch();
          $query->close();
          
          // If there are no references, delete the namespace from active keywords table
          if ($references < 1) {
            $query = $db->prepare('DELETE FROM `keywords` WHERE `NamespaceID` = ?');
            $query->bind_param('i', $currentNamespaceID);
            $query->execute();
            $query->close();
          }
        }
      }
      
      if ($accountID < 1) {
        throw new ErrorException('Invalid log in state.');
      }
      
      // Make sure that translations without enforced owners are always set to null.
      if ($trans->owner == null || !is_numeric($trans->owner)) {
        $trans->owner = 0;
      }
      
      // Insert the row
      $query = $db->prepare(
        "INSERT INTO `translation` (`Translation`, `Etymology`, `Type`, `Source`, `Comments`, 
        `Tengwar`, `Phonetic`, `LanguageID`, `WordID`, `NamespaceID`, `Index`, `AuthorID`,
        `EnforcedOwner`, `Latest`, `DateCreated`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '1', NOW())"
      );
      $query->bind_param('sssssssiiiiii',
        $trans->translation, $trans->etymology, $trans->type, $trans->source, $trans->comments,
        $trans->tengwar, $trans->phonetic, $trans->language, $word->id, $trans->namespaceID,
        $trans->index, $accountID, $trans->owner
      );
      $query->execute();
      
      $trans->id = $query->insert_id;
      
      $query->close();
      
      // update the keywords table with the new results - but in order to do this, we'll need to normalize
      // the input strings to make sure to avoid collisions
      $nword    = StringWizard::normalize($word->key);
      $nkeyword = StringWizard::normalize($trans->translation);
      
      // insert reference
      $insert = array('key' => $word->key, 'nkey' => $nword, 'transID' => $trans->id, 'wordID' => $word->id);
      
      // The word key is always associated with this translation entry
      $query = $db->prepare('INSERT INTO `keywords` (`Keyword`, `NormalizedKeyword`, `TranslationID`, `WordID`) VALUES(?,?,?,?)');
      $query->bind_param('ssii', $insert['key'], $insert['nkey'], $insert['transID'], $insert['wordID']);
      $query->execute();
      
      // The translation field might contain information interesting in regards to its relevance. If this information
      // is not equal to the word already associated with the new entry, add the it as well.
      if ($nword !== $nkeyword && !preg_match('/^\\s*$/', $nkeyword)) {
        $keywordObj = new Word();
        $keywordObj->create($trans->translation);
        
        $insert['key']     = $trans->translation;
        $insert['nkey']    = $nkeyword;
        $insert['transID'] = $trans->id;
        $insert['wordID']  = $keywordObj->id;
        
        $query->bind_param('ssii', $insert['key'], $insert['nkey'], $insert['transID'], $insert['wordID']);
        $query->execute();
      }
      
      $query->close();
      
      return $trans->index ? $trans : $word;
    }
  }
?>
