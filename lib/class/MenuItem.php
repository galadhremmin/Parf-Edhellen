<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class MenuItem {
    public $url;
    public $text;
    public $onclick;
    public $tabIndex;
    public $sectionIndex;
    public $active;
  
    public function __construct($data) {
      $fields = get_object_vars($this);
      
      foreach ($fields as $field => $type) {
        if (isset($data[$field])) {
          $this->$field = $data[$field];
        }
      }
      
      if (isset($this->url) && !empty($this->url)) {
        $url = $_SERVER['REQUEST_URI'];
        
        $pos0 = strrpos($url, '/');
        if ($pos0 === false) {
          $pos0 = -1;
        }
        
        $pos1 = strrpos($this->url, '/');
        if ($pos1 === false) {
          $pos1 = -1;
        }
        
        $this->active = substr($url, $pos0 + 1) === substr($this->url, $pos1 + 1);
      }
    }
  }
?>
