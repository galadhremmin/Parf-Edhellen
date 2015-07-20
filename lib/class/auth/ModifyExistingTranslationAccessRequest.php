<?php

  namespace auth;

  class ModifyExistingTranslationAccessRequest extends BasicAccessRequest {

    private $_translationID;

    public function __construct($translationID) {
      $this->_translationID = intval($translationID);
    }

    public function request(Credentials &$credentials) {
      if (! parent::request($credentials)) {
        return false;
      }

      return $credentials->account()->isAdministrator();
    }

  }