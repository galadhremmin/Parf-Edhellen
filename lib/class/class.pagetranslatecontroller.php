<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageTranslateController extends Controller {
    public function __construct(TemplateEngine &$engine) {
      parent::__construct('translate');
      
      if ($this->_model !== null) {
        $engine->assign('namespaces',   $this->_model->getNamespaces());
        $engine->assign('translations', $this->_model->getTranslations());
        $engine->assign('indexes',      $this->_model->getIndexes());
        $engine->assign('revisions',    $this->_model->getRevisions());
        $engine->assign('languages',    $this->_model->getLanguages());
        $engine->assign('types',        $this->_model->getTypes());
        $engine->assign('loggedIn',     $this->_model->getLoggedIn());
        $engine->assign('term',         $this->_model->getTerm());
        $engine->assign('wordExists',   $this->_model->getWordExists());
      }
    }
  }
?>