<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageTranslateController extends Controller {
    public function __construct(TemplateEngine &$engine) {
      $doCache = false;
      $cacheTag = null;
      
      if (isset($_REQUEST['term'])) {
        $term = $_REQUEST['term'];
        
        $doCache = true;
        $cacheTag = 'translation.term.'.sha1($term);
      }
    
      parent::__construct('translate', $doCache, $cacheTag);
      
      $model = $this->getModel();
      if ($model !== null) {
        $engine->assign('namespaces',   $model->getNamespaces());
        $engine->assign('translations', $model->getTranslations());
        $engine->assign('indexes',      $model->getIndexes());
        $engine->assign('revisions',    $model->getRevisions());
        $engine->assign('languages',    $model->getLanguages());
        $engine->assign('types',        $model->getTypes());
        $engine->assign('loggedIn',     $model->getLoggedIn());
        $engine->assign('term',         $model->getTerm());
        $engine->assign('wordExists',   $model->getWordExists());
        $engine->assign('accountID',    $model->getAccountID());
        $engine->assign('timeElapsed',  $this->getTimeElapsed());
      }
    }
  }
?>