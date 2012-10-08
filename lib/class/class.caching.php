<?php

  class Caching {
    private $_tag;
    private $_lifeTime;
    
    public function __construct($lifeTimeMinutes, $tag = null) {
      if ($tag == null) {
        $tag = self::getDefaultTag();
      }
      
      $this->_tag = 'cache.'.$tag;
      $this->_lifeTime = $lifeTimeMinutes * 60 * 1000;
    }
    
    public static function getDefaultTag() {
      $hash = array();
      foreach ($_GET as $key => $value) {
        // ignore phpsessid keys, as these uniquely identifies sessions
        if (strtolower($key) == 'phpsessid') {
          continue;
        }
        
        $hash[] = $key.'.'.$value;
      }
      
      // combine the query string elements by dots
      $hash = implode('.', $hash);
      
      if (strlen($hash) > 0) {
        $hash = '.'.$hash;
      }
      
      // get the document without the extension
      $document = substr($_SERVER['PHP_SELF'], 0, -4);
      
      return strtolower(
        trim(
          preg_replace(
            '/[^a-zA-Z0-9\\.]/', 
            '', 
            StringWizard::normalize($document.$hash)
          )
        )
      );
    }
    
    public function load() {
      $f = fopen(self::getPath(), 'r');
      $content = '';
      
      if (flock($f, LOCK_SH)) {
        
        fgets($f); // skip the first line (header blocker)
        while (!feof($f)) {
          $content .= fread($f, 4096);
        }
        
        flock($f, LOCK_UN);
      }
      
      return $content;
    }
    
    public function save($content) {
      $content = "<?php exit; ?>\n".$content;
      
      $f = fopen(self::getPath(), 'w');
      
      if (flock($f, LOCK_EX)) {
        fwrite($f, $content);
        flock($f, LOCK_UN);
      }
      
      fclose($f);
    }
    
    public function hasExpired() {
      $path = self::getPath();
      
      if (!file_exists($path)) {
        return true;
      }
      
      return time() - filemtime($path) > $this->_lifeTime;
    }
    
    public function getPath() {
      return ROOT.'cache/'.$this->_tag.'.php';
    }
  }