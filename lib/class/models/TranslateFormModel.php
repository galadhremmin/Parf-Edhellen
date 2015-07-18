<?php
  namespace models;
  
  class TranslateFormModel {
    private $_languages;
    private $_wordClasses;
    private $_wordGenders;
    private $_original;
    private $_reviewID;
    private $_indexes;
    private $_justification;
    private $_resubmission;
    
    public function __construct() {
      $this->_languages     = \data\entities\Language::getLanguageArray(true);
      $this->_wordClasses   = \data\entities\Translation::getTypes();
      $this->_wordGenders   = \data\entities\Word::getWordGenders();
      $this->_original      = new \data\entities\Translation();
      $this->_reviewID      = null;
      $this->_resubmission  = false;
      $this->_justification = '';

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

      if (isset($_GET['reviewID']) && is_numeric($_GET['reviewID'])) {
        $id = intval($_GET['reviewID']);
        $review = new \data\entities\TranslationReview();
        $review->load($id);

        $account = \auth\Credentials::current()->account();
        if ($review->reviewID !== 0 && ($account->isAdministrator() || $account->id == $review->authorID) &&
            $review->approved !== true) {
          // Feed the original values with the review data
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

          // Retrieve indexers, and if none exist, create a blank array to indicate an empty set.
          $indexes = $review->data['indexes'];
          if (!is_array($indexes)) {
            $indexes = array();
          }

          $this->_indexes = $indexes;

          // If there's a justification, retrieve it.
          $this->_justification = $review->justification or '';
          $this->_resubmission  = ($review->approved === false);

          // Append a dot in case the justification message lacks proper interpunctuation ;)
          if (! empty($this->_justification)) {
            $c = $this->_justification[strlen($this->_justification) - 1];
            if ($c !== '.' && $c !== '!') {
              $this->_justification .= '.';
            }
          }

          // Attach the review ID to existing reviews. If the person is trying to resubmit the review item, force
          // the system to create a new one instead.
          $this->_reviewID = $this->_resubmission ? 0 : $id;
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

    public function getJustification() {
      return $this->_justification;
    }

    public function isResubmission() {
      return $this->_resubmission;
    }
  }
