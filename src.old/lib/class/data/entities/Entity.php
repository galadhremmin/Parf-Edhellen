<?php
  namespace data\entities;
  
  abstract class Entity {
  
    public function __construct($data = null) {
      if ($data !== null && is_array($data)) {
        $fields = get_object_vars($this);
      
        foreach ($fields as $field => $type) {
          if (isset($data[$field])) {
            $value = $data[$field];
          
            $this->$field = $value;
          }
        }
      }
    }
    
    public function toJSON() {
      return json_encode($this);
    }
  
    public abstract function validate();
    public abstract function load($numericId);
    public abstract function save();
  }
