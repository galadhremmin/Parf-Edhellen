<?php
  namespace auth;
  
  class WordAccessRequest implements IAccessRequest {
    private $_word;
    private $_access;
  
    public function __construct(\data\entities\Word &$word, $access) {
      $this->_word   = $word;
      $this->_access = $access;
    }
    
    public function request(Credentials &$credentials) {
      $account =& $credentials->account();
      $groups  =& $credentials->groups();
      $group   = 'Users';
      
      $rights = array(
        // AccessRight::CREATE, <-- included in Users.
        AccessRight::MODIFY,
        AccessRight::DELETE,
        AccessRight::ALL
      );
      
      foreach ($rights as $right) {
        if (($this->_access & $right) === $right && $this->_word->owner !== $account->id) {
          $group = 'Administrators';
          break;
        }
      }
      
      return in_array($group, $groups);
    }
    
  }
