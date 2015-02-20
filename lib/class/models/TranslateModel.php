<?php
  namespace models;
  
  class TranslateModel {
    private $_term;
    private $_keywordIndexes;
    private $_translations;
    private $_loggedIn;
    private $_languages;
    private $_wordExists;
    private $_senses;
    
    public function __construct() {
      if (!isset($_REQUEST['term'])) {
        return;
      }
      
      $this->_term = $_REQUEST['term'];
      
      if (get_magic_quotes_gpc()) {
        $this->_term = stripslashes($this->_term);
      }

      // Search term, without specila characters.
      $this->_term = \utils\StringWizard::normalize($this->_term);

      // Retrieve translation data for the specified term
      $data = \data\entities\Translation::translate($this->_term);
      
      if ($data !== null) {
        $this->_senses         = $data['senses'];
        $this->_translations   = $data['translations'];
        $this->_wordExists     = true;
        $this->_keywordIndexes = array();
        
        // Filter out repeated indexes, and preserve only the index word. 
        foreach ($data['keywordIndexes'] as $index) {
          if (!in_array($index->word, $this->_keywordIndexes)) {
            $this->_keywordIndexes[] = $index->word;
          }
        }
      } 
              
      // Retrieve all available languages
      $languages = \data\entities\Language::getAllLanguages();
      $this->_languages = array();

      foreach ($languages as $language) {
        $this->_languages[$language->name] = $language;
      }
      
      // Has the current user authenticated herself?
      $this->_loggedIn = \auth\Session::isValid();
    }
    
    public function getTerm() {
      return $this->_term;
    }
    
    public function getTranslations() {
      return $this->_translations;
    }
        
    public function getLoggedIn() {
      return $this->_loggedIn;
    }
    
    public function getLanguages() {
      return $this->_languages;
    }
        
    public function getWordExists() {
      return $this->_wordExists;
    } 
    
    public function getSenses() {
      return $this->_senses;
    }
    
    public function getIndexes() {
      return $this->_keywordIndexes;
    }
    
    public function getAccountID() {
      $account = \auth\Session::getAccount();
      if ($account === null) {
        return 0;
      }
      return $account->id;
    }
  }
?>
