<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageHeaderModel {
    private $_menu;
    public function __construct() {
      $menu = array();
      
      $menu[] = new MenuItem(array('url' => 'index.page', 'text' => 'Home'));
      $menu[] = new MenuItem(array('url' => 'about.page', 'text' => 'About'));
      $menu[] = new MenuItem(array('url' => 'contributors.page', 'text' => 'Contributors'));
      
      if (Session::isValid()) {
//        $menu[] = new MenuItem(array('url' => '#', 'text' => 'Extend', 'onclick' => 'return LANGDict.showForm(0)'));
        $menu[] = new MenuItem(array('url' => 'profile.page', 'text' => 'Profile'));
        $menu[] = new MenuItem(array('url' => 'exec/deauthenticate.php', 'text' => 'Log out'));
      } else {
        $menu[] = new MenuItem(array('url' => 'authenticate.page', 'text' => 'Log in'));
      }
      
      $this->_menu = $menu;
    }
    
    public function getMenu() {
      return $this->_menu;
    }
  }
?>