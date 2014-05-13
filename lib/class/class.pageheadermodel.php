<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageHeaderModel {
    private $_menu;
    private $_languages;
    
    public function __construct() {
      $menu = array();
      
      $menu[] = new MenuItem(array('url' => 'index.page',        'text' => 'Home',         'sectionIndex' => 1));
      $menu[] = new MenuItem(array('url' => 'about.page',        'text' => 'About',        'sectionIndex' => 1));
      $menu[] = new MenuItem(array('url' => 'contributors.page', 'text' => 'Contributors', 'sectionIndex' => 1));
      $menu[] = new MenuItem(array('url' => 'news.page',         'text' => 'Activity',     'sectionIndex' => 1));
      $menu[] = new MenuItem(array('url' => 'resources.page',    'text' => 'Resources',    'sectionIndex' => 1));
      
      if (Session::isValid()) {
        $menu[] = new MenuItem(array('url' => 'profile.page',            'text' => 'Profile', 'sectionIndex' => 2));
        $menu[] = new MenuItem(array('url' => 'exec/deauthenticate.php', 'text' => 'Log out', 'sectionIndex' => 2));
      } else {
        $menu[] = new MenuItem(array('url' => 'authenticate.page',       'text' => 'Log in',  'sectionIndex' => 2));
      }
      
      $index = 0;
      foreach ($menu as $item) {
        $item->tabIndex = ++$index;
      }
      
      $this->_menu = $menu;
      
      //  And lastly, go with languages
      $this->_languages = array('0' => 'All languages');
      $languages = Language::getLanguageArray(false);
      
      foreach ($languages as $id => $name)
        $this->_languages[$id] = $name;
    }
    
    public function getMenu() {
      return $this->_menu;
    }
    
    public function getLanguages() {
      return $this->_languages;
    }
  }
?>
