<?php
  namespace auth;
  
  class TranslationAccessRequest extends BasicAccessRequest {
    private $_translationID;
  
    public function __construct($translationID) {
      $this->_translationID = $translationID;
    }
    
    public function request(Credentials &$credentials) {
      if (! parent::request($credentials)) {
        return false;
      }
      
      $db = \data\Database::instance();
      $query = $db->connection()->prepare(
        'SELECT COUNT(*) FROM `translation` FROM `translation`
         WHERE `TranslationID` = ? AND (`EnforcedOwner` = 0 OR `EnforcedOwner` = ?)'
      );
      $query->bind_param('ii', $this->_translationID, $credentials->account()->id);
      $query->execute();
      $query->bind_result($count);
      $query->fetch();
      $query->close();

      return $count > 0;
    }
    
  }
