<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageHeaderModel {
    private $_menu;
    private $_backgroundFile;
    private $_backgroundFiles;
    private $_languages;
    private $_viewportWidth;
    
    public function __construct() {
      $menu = array();
      
      $menu[] = new MenuItem(array('url' => 'index.page', 'text' => 'Home'));
      $menu[] = new MenuItem(array('url' => 'about.page', 'text' => 'About'));
      $menu[] = new MenuItem(array('url' => 'contributors.page', 'text' => 'Contributors'));
      $menu[] = new MenuItem(array('url' => 'news.page', 'text' => 'Activity'));
      $menu[] = new MenuItem(array('url' => 'resources.page', 'text' => 'Resources'));
      
      if (Session::isValid()) {
        $menu[] = new MenuItem(array('url' => 'profile.page', 'text' => 'Profile'));
        $menu[] = new MenuItem(array('url' => 'exec/deauthenticate.php', 'text' => 'Log out'));
      } else {
        $menu[] = new MenuItem(array('url' => 'authenticate.page', 'text' => 'Log in'));
      }
      
      $index = 0;
      foreach ($menu as $item) {
        $item->tabIndex = ++$index;
      }
      
      $this->_menu = $menu;
      
      // Load random background
      $backgroundFiles = new DirectoryIterator(ROOT.'img/backgrounds');
      $files = array();
      foreach ($backgroundFiles as $file) {
        if ($file->isDot()) {
          continue;
        }
        $files[] = $file->getFilename();
      }
      
      srand(time());
      $file = array_splice($files, array_rand($files), 1);
      $file = $file[0];
      
      $this->_backgroundFile  = $file;
      $this->_backgroundFiles = $files;
      
      //  And lastly, go with languages
      $this->_languages = array('0' => 'All languages');
      $languages = Language::getLanguageArray(false);
      
      foreach ($languages as $id => $name)
        $this->_languages[$id] = $name;
      
      $this->_viewportWidth = 0;
    }
    
    public function getMenu() {
      return $this->_menu;
    }
    
    public function getBackgroundFile() {
      return $this->_backgroundFile;
    }
    
    public function getBackgroundFiles() {
      return $this->_backgroundFiles;
    }
    
    public function getLanguages() {
      return $this->_languages;
    }
    
    public function getViewportWidth() {
      if ($this->_viewportWidth < 1) {
         $mobileFound = false;
         $browsers = array('/iphone/i', '/windows\sphone/i', '/android/i');
         $browser = $_SERVER['HTTP_USER_AGENT'];
         
         foreach ($browsers as $reg) {
            $mobileFound = preg_match($reg, $browser);
            if ($mobileFound) {
               break;
            }
         }
          
         $this->_viewportWidth = $mobileFound ? 480 : 950;
      }
    
      return $this->_viewportWidth;
    }
  }
?>