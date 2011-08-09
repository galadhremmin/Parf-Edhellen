<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageProfileController extends Controller {
    public function __construct(&$engine) {
      parent::__construct('profile');
    
      if ($this->_model !== null) {
        $engine->assign('loggedIn',      $this->_model->getLoggedIn());
        $engine->assign('myProfile',     $this->_model->getLoadedAuthenticatedAuthor());
        $engine->assign('author',        $this->_model->getAuthor());
        $engine->assign('accountAuthor', $this->_model->getAuthorForAccount());
      }
    }
  }
?>