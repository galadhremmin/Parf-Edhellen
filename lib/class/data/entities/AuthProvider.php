<?php

  namespace data\entities;
  
  class AuthProvider extends Entity {
    
    public $id;
    public $name;
    public $url;
    public $logo;
    
    public function __construct(array $data = null) {
      parent::__construct($data);
    }
    
    public function load($id) {
      $db = \data\Database::instance();
      
      $query = null;
      try {
        $query = $db->connection()->prepare(
            'SELECT `ProviderID`, `Name`, `Logo`, `URL` FROM `auth_providers` WHERE `ProviderID` = ?'
        );
        $query->bind_param('i', $id);
        $query->execute();
        $query->bind_result($this->id, $this->name, $this->logo, $this->url);
        
        if (!$query->fetch()) {
          $this->id = 0;
          $this->name = null;
        }
      } finally {
        if ($query !== null) {
          $query->close();
        }
      }
    }
    
    public function validate() {
      if (empty($this->name) || is_null($this->name)) {
        return false;
      }
      
      if (empty($this->url) || is_null($this->url)) {
        return false;
      }
      
      return true;
    }
    
    public function save() {
      throw new \exceptions\NotImplementedException(__METHOD__);
    }
    
    public static function getAllProviders() {
      $db = \data\Database::instance();
      
      $query = null;
      
      try {
        $query = $db->connection()->query(
            'SELECT `ProviderID`, `Name`, `Logo`, `URL` FROM `auth_providers` ORDER BY `Name` ASC'
        );
        
        $providers = array();
        while ($row = $query->fetch_object()) {
          $providers[] = new AuthProvider(array(
              'id'   => $row->ProviderID,
              'name' => $row->Name,
              'logo' => $row->Logo,
              'url'  => $row->URL
          ));
        }
      } finally {
        $query = null;
      }
      
      return $providers;
    }
  }