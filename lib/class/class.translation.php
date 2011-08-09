<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class Translation {
    public $word;
    public $id;
    public $translation;
    public $etymology;
    public $type;
    public $source;
    public $comments;
    public $tengwar;
    public $phonetic;
    public $language;
    public $namespaceID;
  
    public function __construct($data) {
      $fields = get_object_vars($this);
      
      foreach ($fields as $field => $type) {
        if (isset($data[$field])) {
          $this->$field = $data[$field];
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