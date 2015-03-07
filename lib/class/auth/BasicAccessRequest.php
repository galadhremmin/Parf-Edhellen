<?php
  namespace auth;
  
  class BasicAccessRequest implements IAccessRequest {
    
    public function request(Credentials &$credentials) {
      $groups =& $credentials->groups();
      return in_array('Users', $groups);
    }
    
  }
