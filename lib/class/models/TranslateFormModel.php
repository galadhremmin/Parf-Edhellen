<?php
  namespace models;
  
  class TranslateFormModel {
    private $_languages;
    private $_wordClasses;
    private $_wordGenders;
    private $_original;
    private $_reviewID;
    private $_indexes;
    
    public function __construct() {
      $this->_languages   = \data\entities\Language::getLanguageArray(true);
      $this->_wordClasses = \data\entities\Translation::getTypes();
      $this->_wordGenders = \data\entities\Word::getWordGenders();
      $this->_original    = new \data\entities\Translation();
      $this->_reviewID    = null;

      if (isset($_GET['translationID']) && is_numeric($_GET['translationID'])) {
        $id = intval($_GET['translationID']);
        $this->_original->load($id);

        // Compile all indexes associated with the specified translation.
        $this->_indexes = array();
        $indexRefs = $this->_original->getIndexes();
        foreach ($indexRefs as $indexRef) {
          // Don't add duplicates.
          if (!in_array($indexRef['word'], $this->_indexes)) {
            $this->_indexes[] = $indexRef['word'];
          }
        }
      }

      if (isset($_GET['reviewID']) && is_numeric($_GET['reviewID']) && \auth\Credentials::current()->account()->isAdministrator()) {
        $id = intval($_GET['reviewID']);
        $review = new \data\entities\TranslationReview();
        $review->load($id);

        if ($review->reviewID !== 0) {
          $this->_reviewID = $id;

          $this->_original->translation = $review->data['translation'];
          $this->_original->comments    = $review->data['comments'];
          $this->_original->word        = $review->data['word'];
          $this->_original->source      = $review->data['source'];
          $this->_original->language    = $review->data['language'];
          $this->_original->etymology   = $review->data['etymology'];
          $this->_original->tengwar     = $review->data['tengwar'];
          $this->_original->type        = $review->data['type'];
          $this->_original->phonetic    = $review->data['phonetic'];
          $this->_original->gender      = $review->data['gender'];

          $this->_indexes = $review->data['indexes'];
        }
      }
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
    
    public function &getOriginal() {
      return $this->_original;
    }

    public function getReviewID() {
      return $this->_reviewID;
    }

    public function getIndexes() {
      return $this->_indexes;
    }
  }
