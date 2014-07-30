<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageProfileController extends Controller {
    public function __construct(TemplateEngine &$engine) {
      parent::__construct('profile', $engine);
    }
    
    public function load() {
      $model = $this->getModel();
      if ($model !== null) {
        $engine = $this->_engine;
        $engine->assign('loggedIn',      $model->getLoggedIn());
        $engine->assign('myProfile',     $model->getLoadedAuthenticatedAuthor());
        $engine->assign('author',        $model->getAuthor());
        $engine->assign('accountAuthor', $model->getAuthorForAccount());
      }
    }
  }
?>
