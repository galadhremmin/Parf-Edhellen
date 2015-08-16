<?php
  namespace models;

  use data\entities\Language;

  class SentenceFormModel {
    private $_sentence;
    private $_languages;

    public function __construct() {
      $this->_sentence = '';
      $this->_fragments = array();

      $this->_sentence = new \data\entities\Sentence();
      $this->_languages = \data\entities\Language::getLanguageArray();

      if (isset($_REQUEST['SentenceID'])) {
        $id = intval($_REQUEST['SentenceID']);
        $this->_sentence->load($id);
      } else {
        $this->_sentence->language = new Language();
      }
    }

    public function getSentence() {
      return $this->_sentence;
    }

    public function getLanguages() {
      return $this->_languages;
    }
  }