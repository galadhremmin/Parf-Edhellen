<?php
  namespace controllers;
  
  class TranslateController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      $doCache = false;
      $cacheTag = null;
      
      if (isset($_REQUEST['term'])) {
        $term = $_REQUEST['term'];
        
        $doCache = true;
        $cacheTag = 'translation.term.'.sha1($term);
      }
    
      parent::__construct('Translate', $engine, $doCache, $cacheTag);
    }
    
    public function load() {
      $model = $this->getModel();
      if ($model !== null) {
        $engine = $this->_engine;
        $engine->assign('senses',       $model->getSenses());
        $engine->assign('translations', $model->getTranslations());
        $engine->assign('indexes',      $model->getIndexes());
        $engine->assign('languages',    $model->getLanguages());
        $engine->assign('loggedIn',     $model->getLoggedIn());
        $engine->assign('term',         $model->getTerm());
        $engine->assign('wordExists',   $model->getWordExists());
        $engine->assign('accountID',    $model->getAccountID());
        $engine->assign('timeElapsed',  $this->getTimeElapsed());
        
        $this->assignColumnWidths($model, $engine);
      }
    }
    
    private function assignColumnWidths(\models\TranslateModel &$model, \TemplateEngine &$engine) {
      $translations = $model->getTranslations();
      if (!is_array($translations)) {
        return;
      }

      $numberOfLanguages = count(array_keys($translations));
        
      $max = 12;
      $mid = $numberOfLanguages > 1 ? 6 : $max;
      $min = $numberOfLanguages > 2 ? 4 : $mid;
      
      $engine->assign('maxColumnWidth', $max);
      $engine->assign('midColumnWidth', $mid);
      $engine->assign('minColumnWidth', $min);
    }
  }
?>
