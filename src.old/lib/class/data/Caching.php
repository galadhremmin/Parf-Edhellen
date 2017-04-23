<?php
  namespace data;

  class Caching {
    protected $_tag;      // these are `protected` due to friendliness to inheritance
    protected $_lifeTime;
    
    public function __construct($lifeTimeMinutes, $tag = null) {
      if ($tag == null) {
        $tag = self::getDefaultTag();
      }
      
      $this->_tag = 'cache.'.$tag;
      $this->_lifeTime = $lifeTimeMinutes * 60;
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
            \utils\StringWizard::normalize($document.$hash)
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
    
    public function getRemainingLifetime() {
      if (defined('SYS_NO_CACHE') && constant('SYS_NO_CACHE') === true) {
        return 0;
      }
    
      $path = self::getPath();
      
      if (!file_exists($path)) {
        return 0;
      }
      
      $lifetime = $this->_lifeTime + filemtime($path) - time();
      return $lifetime;
    }
    
    public function hasExpired() {
      return $this->getRemainingLifetime() < 1;
    }
    
    public function getPath() {
      return ROOT.'cache/'.$this->_tag.'.php';
    }
  }
