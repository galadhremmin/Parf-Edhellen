<?php
  namespace models;
  
  class ProfileModel {
    private $_loggedIn;
    private $_loadedAuthenticatedAuthor;
    private $_author;
    private $_authorForAccount;
  
    public function __construct() {
      $id = 0;      
      if (isset($_GET['authorID'])) {
        $this->_loadedAuthenticatedAuthor = false;
        $id = $_GET['authorID'];
      } else {
        $this->_loadedAuthenticatedAuthor = true;
        $id = \auth\Session::getAccountID();
      }
      
      if (!is_numeric($id) || $id < 1) {
        return;
      }
      
      $this->_loggedIn = \auth\Session::isValid();
      
      //
      // $author is the public face, and all its values will be appropriately
      // formatted in order to prevent XSS and similiar attacks based on HTML/
      // javascript-injection. The $authorForAccount is however the object used
      // for filling in the forms for editing the user's profile. It cannot 
      // invoke createLinks as this would yield HTML instead of its appropriate 
      // markup.
      //
      $author           = new \data\entities\Author();
      $authorForAccount = null;
     
      // Attempt to load the author by its ID
      $author->load($id);
     
      // Examine all necessary values before they are passed on to the
      // view for presentation
      $author->nickname = \utils\StringWizard::preventXSS($author->nickname);
      $author->tengwar  = \utils\StringWizard::preventXSS($author->tengwar);
      
      // If the user is logged in, there will also be a form where the user
      // can change his/her own profile. Thus, create a where the dictionary
      // markup isn't replaced by HTML.
      if ($this->_loggedIn) {
        $authorForAccount = clone $author;
        $authorForAccount->profile = \utils\StringWizard::preventXSS($author->profile);
      }
      
      // Replace _markup_ with <em>html</em>.
      $author->profile = \utils\StringWizard::createLinks($author->profile);
      
      $this->_author = $author;
      $this->_authorForAccount = $authorForAccount;
    }
    
    public function getLoggedIn() {
      return $this->_loggedIn;
    }
    
    public function getLoadedAuthenticatedAuthor() {
      return $this->_loadedAuthenticatedAuthor;
    }
    
    public function getAuthor() {
      return $this->_author;
    }
    
    public function getAuthorForAccount() {
      return $this->_authorForAccount;
    }
  }
