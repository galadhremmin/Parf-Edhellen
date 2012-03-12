<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class MenuItem {
    public $url;
    public $text;
    public $onclick;
    public $tabIndex;
  
    public function __construct($data) {
      $fields = get_object_vars($this);
      
      foreach ($fields as $field => $type) {
        if (isset($data[$field])) {
          $this->$field = $data[$field];
        }
      }
    }
  }
?>