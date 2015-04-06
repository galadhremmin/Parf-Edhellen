<?php
  namespace auth;
  
  class TranslationAccessRequest extends BasicAccessRequest {
    private $_translationID;
  
    public function __construct($translationID) {
      parent::__construct();
      $this->_translationID = $translationID;
    }
    
    public function request(Credentials &$credentials) {
      if (! parent::request($credentials) || ! is_numeric($this->_translationID)) {
        return false;
      }
      
      if ($this->_translationID == 0) {
        return true;
      }

      $db = \data\Database::instance();
      $query = $db->connection()->prepare(
        'SELECT COUNT(*) FROM `translation` 
         WHERE `TranslationID` = ? AND `EnforcedOwner` IN (0, ?)'
      );
      $query->bind_param('ii', $this->_translationID, $credentials->account()->id);
      $query->execute();
      $query->bind_result($count);
      $query->fetch();
      $query->close();

      return $count > 0;
    }
    
  }
