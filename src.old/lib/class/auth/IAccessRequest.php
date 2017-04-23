<?php
  namespace auth;
  
  interface IAccessRequest {
    
    public function request(Credentials& $credentials);
    
  }
