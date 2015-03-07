<?php
  namespace models;
  
  class ProfileModel {
    private $_loggedIn;
    private $_loadedAuthenticatedAuthor;
    private $_author;
  
    public function __construct() {
      try {
        $credentials = \auth\Credentials::request(new \auth\BasicAccessRequest());
      } catch (\exceptions\InadequatePermissionsException $ex) {
        $credentials = null;
      }
    
      $id = 0;
      if (isset($_GET['authorID'])) {
        $this->_loadedAuthenticatedAuthor = false;
        $id = $_GET['authorID'];
      } else {
        $this->_loadedAuthenticatedAuthor = true;
        $id = $credentials !== null 
          ? $credentials->account()->id 
          : 0;
      }
      
      if (! is_numeric($id) || $id < 1) {
        return;
      }
      
      $this->_loggedIn = $credentials !== null;
      
      //
      // $author is the public face, and all its values will be appropriately
      // formatted in order to prevent XSS and similiar attacks based on HTML/
      // javascript-injection. The $authorForAccount is however the object used
      // for filling in the forms for editing the user's profile. It cannot 
      // invoke createLinks as this would yield HTML instead of its appropriate 
      // markup.
      //
      $author           = new \data\entities\Account();
      $authorForAccount = null;
     
      // Attempt to load the author by its ID
      $author->load($id, true);
     
      // Examine all necessary values before they are passed on to the
      // view for presentation
      $author->nickname = \utils\StringWizard::preventXSS($author->nickname);
      $author->tengwar  = \utils\StringWizard::preventXSS($author->tengwar);
      
      $this->_author = $author;
    }
    
    public function getLoggedIn() {
      return $this->_loggedIn;
    }
    
    public function hasLoadedAuthenticatedAuthor() {
      return $this->_loadedAuthenticatedAuthor;
    }
    
    public function getAuthor() {
      return $this->_author;
    }
  }
