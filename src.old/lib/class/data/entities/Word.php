<?php
  namespace data\entities;

  class Word extends OwnableEntity {
    public $id;
    public $key;
    public $authorID;
  
    public function __construct($data = null) {
      parent::__construct($data);
    }
    
    public function load($id) {
      $db = \data\Database::instance();
      
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
      return  $this->key != null && !preg_match('/^\\s*$/', $this->key) && 
             ($this->id === null || is_numeric($this->id));
    }
    
    public function create($key) {
      $this->id = 0;
      $this->key = $key;
      
      return $this->save();
    }
    
    public function save() {
      if ($this->id) {
        throw new \ErrorException('Word has already been assigned an ID and consequently exists already.');
      }
      
      if (!$this->validate()) {
        throw new \ErrorException('Invalid or malformed Word-object.');
      }
      
      $credentials =& \auth\Credentials::request(new \auth\WordAccessRequest($this, \auth\AccessRight::CREATE));
      
      // exclusive connections require the current account to be authenticated 
      $db = \data\Database::instance()->connection();
      
      $query  = null;
      $exists = false;
      try {
        $query = $db->prepare(
          'SELECT `KeyID` FROM `word` WHERE BINARY `Key` = ?'
        );
        
        $query->bind_param('s', $this->key);
        $query->execute();
        $query->bind_result($this->id);
        
        $exists = ($query->fetch() === true);
      } finally {
        if ($query !== null) {
          $query->close();
        }
      }
      
      // doesn't exist
      if (! $exists) {        
        $this->authorID = $credentials->account()->id;
        
        $query = null;
        try {
          $query = $db->prepare(
            'INSERT INTO `word` (`Key`, `NormalizedKey`, `ReversedNormalizedKey`, `AuthorID`) VALUES (?, ?, ?, ?)'
          );
          
          $normalizedKey = \utils\StringWizard::normalize($this->key);
          $reversedNormalizedKey = strrev($normalizedKey);
          $accountID = $this->authorID;
          
          $query->bind_param('sssi', $this->key, $normalizedKey, $reversedNormalizedKey, $accountID);
          $query->execute();
          
          $this->id = $query->insert_id;
        } finally {
          if ($query !== null) {
            $query->close();
          }
        }
        
      } else {
        $this->load($this->id);
      }
      
      return $this;
    }
    
    public static function getWordClasses() {
      $classes = array();
      $query = null;
      
      try {
        $query = \data\Database::instance()->connection()->query(
          'SELECT `GrammarTypeID`, `Name` FROM `grammar_type` ORDER BY `Name` ASC'
        );
        
        while ($row = $query->fetch_assoc()) {
          $classes[$row['GrammarTypeID']] = $row['Name'];
        }
        
      } finally {
        if ($query !== null) {
          $query->close();
        }
      }
      
      return $classes;
    }
    
    public static function getWordGenders() {
      return array(
        'none', 'masculine', 'feminine', 'neuter'
      );
    }
    
    public static function unregisterReference($id, $threshold = 1) {
      $db = \data\Database::instance()->connection();
      
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
  }
