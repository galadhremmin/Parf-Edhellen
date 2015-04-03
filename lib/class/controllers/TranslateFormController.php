<?php
  namespace controllers;
  
  class TranslateFormController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('TranslateForm', $engine);
    }
    
    public function load() {      
      $this->_engine->assign('title', 'WIP');

      $model = $this->getModel();
      if ($model !== null) {
        $this->_engine->assign('inventedLanguages', $model->getLanguages());
        $this->_engine->assign('wordClasses',       $model->getWordClasses());
        $this->_engine->assign('wordGenders',       $model->getWordGenders());
        
        $original =& $model->getOriginal();
        $this->_engine->assign('id',                $original->id);
        $this->_engine->assign('orig_language',     $original->language);
        $this->_engine->assign('orig_word',         $original->word);
        $this->_engine->assign('orig_translation',  $original->translation);
        $this->_engine->assign('orig_comments',     $original->comments);
        $this->_engine->assign('orig_source',       $original->source);
        $this->_engine->assign('orig_etymology',    $original->etymology);
        $this->_engine->assign('orig_tengwar',      $original->tengwar);
        $this->_engine->assign('orig_type',         $original->type);
        $this->_engine->assign('orig_gender',       $original->gender);
        
        $indexRefs = $original->getIndexes();
        $indexes   = array();
        foreach ($indexRefs as $indexRef) {
          $indexes[] = $indexRef['word'];
        }
        
        $this->_engine->assign('orig_indexes', json_encode($indexes));
      }
    }
  }
