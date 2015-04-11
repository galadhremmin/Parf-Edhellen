<?php
  namespace controllers;
  
  class TranslateFormController extends SecureController {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('TranslateForm', $engine);
    }
    
    public function load() {
    	parent::load();
    	
      $model = $this->getModel();
      if ($model === null) {
      	throw new \exceptions\NotImplementedException(__METHOD__);
      }
      
      $this->bind($model);
    }
    
    private function bind(\models\TranslateFormModel& $model) {
      $this->_engine->assign('inventedLanguages', $model->getLanguages());
      $this->_engine->assign('wordClasses',       $model->getWordClasses());
      $this->_engine->assign('wordGenders',       $model->getWordGenders());
        
      $original =& $model->getOriginal();
      
      // Check whether the current user is permitted to perform changes tot his translation entry.
      if (! \auth\Credentials::permitted(new \auth\TranslationAccessRequest($original->id))) {
      	$original->disassociate();
      }
      
      $this->_engine->assign('id',                $original->id);
      $this->_engine->assign('senseID',           $original->senseID);
      $this->_engine->assign('orig_language',     $original->language);
      $this->_engine->assign('orig_word',         $original->word);
      $this->_engine->assign('orig_translation',  $original->translation);
      $this->_engine->assign('orig_comments',     $original->comments);
      $this->_engine->assign('orig_source',       $original->source);
      $this->_engine->assign('orig_etymology',    $original->etymology);
      $this->_engine->assign('orig_tengwar',      $original->tengwar);
      $this->_engine->assign('orig_type',         $original->type);
      $this->_engine->assign('orig_gender',       $original->gender);
      $this->_engine->assign('orig_phonetic',     $original->phonetic);
        
      $indexRefs = $original->getIndexes();
      $indexes   = array();
      foreach ($indexRefs as $indexRef) {
      	if (!in_array($indexRef['word'], $indexes)) {
        	$indexes[] = $indexRef['word'];
      	}
      }
        
      $this->_engine->assign('orig_indexes', json_encode($indexes));
      $this->_engine->assign('operation', ($original->id > 0 ? 'Edit' : 'Add'));
    }
  }
