<?php

  namespace auth;

  class TranslationReviewAccessRequest extends BasicAccessRequest {

    private $_reviewID;

    public function __construct($reviewID) {
      $this->_reviewID = $reviewID;
    }

    public function request(Credentials &$credentials) {
      if (! parent::request($credentials)) {
        return false;
      }

      if ($this->_reviewID === 0) {
        return true;
      }

      return $credentials->account()->isAdministrator();
    }

  }
