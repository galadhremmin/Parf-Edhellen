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
      
      if (empty($this->url)) {
        $this->active = false;
        return;
      }
      
      // support multiple URLs.
      if (! is_array($this->url)) {
        $this->url = array($this->url);
      }
      
      // find the first matching URL based on the current request. If a match
      // is found, the member "active" is assigned "true".
      $requestUrl = $_SERVER['REQUEST_URI'];
      foreach ($this->url as $url) {
        
        $pos0 = strrpos($requestUrl, '/');
        if ($pos0 === false) {
          $pos0 = -1;
        }
        
        $pos1 = strrpos($url, '/');
        if ($pos1 === false) {
          $pos1 = -1;
        }
        
        $this->active = substr($requestUrl, $pos0 + 1) === substr($url, $pos1 + 1);
        
        if ($this->active) {
          break;
        }
      }
    }
  }
?>
