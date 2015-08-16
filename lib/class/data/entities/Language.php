<?php
  namespace data\entities;
  
  class Language extends Entity {
    public $id;
    public $invented;
    public $name;
    public $tengwar;
  
    public function __construct($data = null) {
      parent::__construct($data);
    }
    
    public function validate() {
      return true;
    }
    
    public function load($id) {
      if (!is_numeric($id)) {
        return $this;
      }
      
      $query = null;
      $db = \data\Database::instance()->connection();
      try {
        
        $query = $db->prepare('SELECT `Name`, `Invented`, `Tengwar` FROM `language` WHERE `ID` = ?');
        $query->bind_param('i', $id);
        $query->execute();
        $query->bind_result($this->name, $this->invented, $this->tengwar);
        
        if ($query->fetch()) {
          $this->id = $id;
        }
        
      } finally {
        if ($query !== null) {
          $query->close();
        }
      }
      
      return $this;
    }
    
    public function save() {
      return $this;
    }

    public function __toString() {
      return (string) $this->name;
    }
    
    public static function getAllLanguages() {
      $db = \data\Database::instance()->connection();
      $data = array();
      
      $res = $db->query(
        'SELECT `ID`, `Name`, `Invented`, `Tengwar` FROM `language` ORDER BY `Invented` DESC, `Name` ASC'
      );
      
      while ($row = $res->fetch_assoc()) {
        $data[] = new Language(array(
          'id'       => $row['ID'],
          'name'     => $row['Name'],
          'invented' => $row['Invented'],
          'tengwar'  => $row['Tengwar']
        ));
      }
      
      $res->close();
      
      return $data;
    }
    
    public static function getLanguageArray($excludeRealLanguages = true) {
      $originalData = self::getAllLanguages();
      $data = array();

      foreach ($originalData as $language) {
        if ($excludeRealLanguages && ! $language->invented) {
          continue;
        }
        
        $data[$language->id] = $language->name;
      }
      
      return $data;
    }
  }
