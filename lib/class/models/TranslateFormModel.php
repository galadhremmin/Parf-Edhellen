<?php
  namespace models;
  
  class TranslateFormModel {
    private $_languages;
    private $_wordClasses;
    private $_wordGenders;
    
    public function __construct() {
      $this->_languages   = \data\entities\Language::getLanguageArray(true);
      $this->_wordClasses = \data\entities\Word::getWordClasses();
      $this->_wordGenders = \data\entities\Word::getWordGenders();
    }
    
    public function getLanguages() {
      return $this->_languages;
    }
    
    public function getWordClasses() {
      return $this->_wordClasses;
    }
    
    public function getWordGenders() {
      return $this->_wordGenders;
    }
  }
