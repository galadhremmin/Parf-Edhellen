<?php
  namespace models;
  
  class IndexModel {
    private $_reviews;
    private $_sentence;
    private $_loggedIn;

    public function __construct() {
      $this->_reviews = \data\entities\TranslationReview::getLatestReviewsApproved();
      $this->_sentence = \data\entities\Sentence::getRandomSentence();
      $this->_loggedIn = \auth\Credentials::permitted(new \auth\BasicAccessRequest());
    }

    public function getReviews() {
      return $this->_reviews;
    }

    public function getSentence() {
      return $this->_sentence;
    }

    public function getIsLoggedIn() {
      return $this->_loggedIn;
    }
  }
