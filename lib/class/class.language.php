<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class Language extends Entity {
    public $id;
    public $invented;
    public $name;
  
    public function __construct($data = null) {
      $fields = get_object_vars($this);
      
      foreach ($fields as $field => $type) {
        if (isset($data[$field])) {
          $this->$field = $data[$field];
        }
      }
    }
    
    public function validate() {
      return true;
    }
    
    public function load($numericId) {
      return $this;
    }
    
    public function save() {
      return $this;
    }
  }
?>