<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class Translation {
  
    // Mutable columns
    public $word;
    public $translation;
    public $etymology;
    public $type;
    public $source;
    public $comments;
    public $tengwar;
    public $gender;
    public $phonetic;
    public $language;
    public $namespaceID;
    
    // Semi-mutable column
    public $index;
    
    // Read-only columns
    public $id;
    public $dateCreated;
    public $authorID;
    public $latest;
  
    public function __construct($data = null) {
      
      if ($data !== null && is_array($data)) {
        $fields = get_object_vars($this);
        
        foreach ($fields as $field => $type) {
          if (isset($data[$field])) {
            $this->$field = $data[$field];
          }
        }
      }
    }
    
    public function validate() {
      if (preg_match('/^\\s*$/', $this->word) || 
          preg_match('/^\\s*$/', $this->translation) || 
          $this->language < 1) {
        return false;
      }
      
      return true;
    }
    
    public function load($id) {
      // result container
      
      $db = Database::instance();
      $query = $db->connection()->prepare(
        'SELECT 
          t.`LanguageID`, t.`Translation`, t.`Etymology`, t.`Type`, t.`Source`, t.`Comments`, 
          t.`Tengwar`, t.`Gender`, t.`Phonetic`, w.`Key`, t.`NamespaceID`, t.`AuthorID`,
          t.`DateCreated`, t.`Latest`, t.`Index`
         FROM `translation` t 
         LEFT JOIN `word` w ON w.`KeyID` = t.`WordID`
         WHERE t.`TranslationID` = ?'
      );

      $query->bind_param('i', $id);
      $query->execute();
      $query->bind_result(
        $this->language, $this->translation, $this->etymology, $this->type, $this->source, $this->comments,
        $this->tengwar, $this->gender, $this->phonetic, $this->word, $this->namespaceID, $this->authorID,
        $this->dateCreated, $this->latest, $this->index
      );
      $query->fetch();
      $query->close();
    }
    
    public static function getTypes() {
      $db = Database::instance();
    
      $data = array();
      $query = $db->connection()->query(
        "SHOW COLUMNS FROM `translation` WHERE `Field` = 'Type'"
      );
      
      while ($row = $query->fetch_object()) {
        $values = null;
        if (preg_match_all('/\'([a-zA-Z\\/\\|]+)\'/', $row->Type, $values)) {
          foreach ($values[1] as $value) {
            $data[$value] = str_replace(array('/', '|'), array('. and ', '. or '), $value).'.';
          }
          
          ksort($data);
        }
      }
      
      $query->close();
      
      return $data;
    }
  }
?>