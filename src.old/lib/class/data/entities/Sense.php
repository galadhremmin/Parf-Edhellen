<?php
  namespace data\entities;
  
  class Sense extends OwnableEntity {
    public $id;
    public $identifier;
    
    public static function countByAccount(Account &$account) {
      $db = \data\Database::instance()->connection();
      $query = null;
      try {
        $query = $db->prepare(
          'SELECT COUNT(*) AS `count` FROM `word` w 
             LEFT JOIN `namespace` n ON n.`IdentifierID` = w.`KeyID`
             WHERE w.`AuthorID` = ?'
        );
        $query->bind_param('i', $account->id);
        $query->execute();
        $query->bind_result($count);
        
        if ($query->fetch()) {
          return $count;
        }
      } finally {
        if ($query instanceof \mysqli_stmt) {
          $query->close();
        }
      }
      
      return 0;
    }
  
    public function load($id) {
      if ($id === 0) {
        $this->id = 0;
        $this->identifier = null;
        
        return $this;
      }
    
      $db = \data\Database::instance()->connection();
      $query = $db->prepare(
        'SELECT w.`Key` FROM `namespace` n
         LEFT JOIN `word` w ON w.`KeyID` = n.`IdentifierID`
         WHERE n.`NamespaceID` = ?'
      );
      
      $query->bind_param('i', $id);
      $query->execute();
      $query->bind_result($this->identifier);
      if ($query->fetch()) {
        $this->id = $id;
      }
      $query->close();
      
      return $this;
    }
    
    public function validate() {
      return !preg_match('/^[\\s]*$/', $this->identifier);
    }
    
    public function save() {
      if (! $this->validate()) {
        throw new \ErrorException('Invalid Sense.');
      }
    
      $db = \data\Database::instance()->connection();
      
      $word = new Word();
      $word->create($this->identifier);
      
      $query = $db->prepare(
        'SELECT n.`NamespaceID`
         FROM `namespace` n
         WHERE n.`IdentifierID` = ?'
      );
      $query->bind_param('i', $word->id);
      $query->execute();
      $query->bind_result($this->id);
      
      if ($query->fetch() !== true) {
        $query = $db->prepare(
          'INSERT INTO `namespace` (`IdentifierID`) VALUES (?)'
        );
        $query->bind_param('i', $word->id);
        $query->execute();
        
        $this->id = $db->insert_id;
        $query = null;
        
        // Create a keywords entry
        $nkey = \utils\StringWizard::normalize($word->key);
        $rnkey = strrev($nkey);
        $query = $db->prepare(
          'INSERT INTO `keywords` (`Keyword`, `NormalizedKeyword`, `ReversedNormalizedKeyword`, `NamespaceID`, `WordID`) VALUES (?,?,?,?,?)'
        );
        $query->bind_param('sssii', $word->key, $nkey, $rnkey, $this->id, $word->id);
        $query->execute();
        $query = null;
      } else {
        $query->free_result();
        $query = null;
      }

      return $this;
    }

    /**
     * Retrieves the indexes associated with the sense.
     * @return array
     */
    public function getIndexes() {
      $db = \data\Database::instance();
      $query = $db->connection()->prepare(
        'SELECT DISTINCT
          t.`TranslationID`, t.`WordID`, w.`Key`
         FROM `translation` t
         LEFT JOIN `word` w ON w.`KeyID` = t.`WordID`
         WHERE t.`NamespaceID` = ? AND t.`Index` = \'1\'
         ORDER BY w.`Key` ASC'
      );
      $query->bind_param('i', $this->id);
      $query->execute();
      $query->bind_result(
        $indexID, $wordID, $word
      );

      $indexes = array();
      while ($query->fetch()) {
        $indexes[] = new Translation(array(
          'id'     => $indexID,
          'wordID' => $wordID,
          'word'   => $word,
          'index'  => true
        ));
      }

      $query->free_result();
      $query = null;

      return $indexes;
    }
  }

