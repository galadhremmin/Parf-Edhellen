<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageProfileController extends Controller {
    public function __construct(&$engine) {
      parent::__construct('profile');
    
      $model = $this->getModel();
      if ($model !== null) {
        $engine->assign('loggedIn',      $model->getLoggedIn());
        $engine->assign('myProfile',     $model->getLoadedAuthenticatedAuthor());
        $engine->assign('author',        $model->getAuthor());
        $engine->assign('accountAuthor', $model->getAuthorForAccount());
      }
    }
  }
?>