<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageTranslateModel {
    private $_term;
    private $_keywordIndexes;
    private $_translations;
    private $_revisions;
    private $_loggedIn;
    private $_languages;
    private $_types;
    private $_wordExists;
    private $_namespaces;
    
    public function __construct() {
      if (isset($_REQUEST['term'])) {
        $this->_term = $_REQUEST['term'];
        $db          = Database::instance();
        
        if (get_magic_quotes_gpc()) {
          $this->_term = stripslashes($this->_term);
        }
        
        $this->_term = StringWizard::normalize($this->_term);
        $this->_loggedIn = Session::isValid();
        
        $data = Translation::translate($this->_term);
        
        if ($data !== null) {
          $this->_namespaces     = $data['namespaces'];
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
                
        // Load first data necessary for interaction 
        // Languages
        $languages = array();
        $query = $db->connection()->query(
          'SELECT `ID`, `Name`, `Tengwar` FROM `language` WHERE `Invented` = 1 ORDER BY `Order` ASC'
        );
      
        while ($row = $query->fetch_object()) {
          $languages[$row->Name] = $row;
        }
      
        $query->close();
      
        $this->_languages = $languages;
      
        // Grammar types
        $this->_types = Translation::getTypes();
        
        if ($this->_wordExists && count($this->_namespaces) > 0) {        
          $namespaceIDs = implode(',', array_keys($this->_namespaces));
          
          // Find all revisions
          $query = $db->connection()->query(
            "SELECT t.`TranslationID`, DATE_FORMAT(t.`DateCreated`, '%Y-%m-%d') AS `DateCreated`, 
               t.`Latest`, w.`Key`, a.`Nickname` AS `AuthorName`, a.`AccountID` AS `AuthorID`, w.`NormalizedKey`
             FROM `translation` t
             INNER JOIN `word` w ON w.`KeyID` = t.`WordID`
             INNER JOIN `auth_accounts` a ON a.`AccountID` = t.`AuthorID`
             WHERE t.`NamespaceID` IN(".$namespaceIDs.") AND t.`Index` = '0'
             ORDER BY t.`DateCreated` DESC, w.`Key` ASC, t.`Latest` DESC"
          );
        
          // reset the data array, as it will now contain a different
          // data set
          $data = array();
          while ($row = $query->fetch_object()) {
            $row->Rating = abs(strcmp($row->NormalizedKey, $this->_term));
            $data[] = $row;
          }
 
          $query->close();
          usort($data, array($this, 'ItemCmp'));
                    
          $this->_revisions = $data;
        }
      }
    }

    public static function ItemCmp($a, $b) {
      if ($a->TranslationID == $b->TranslationID) {
        return 0;
      }

      if ($a->Rating == $b->Rating) {
        return strcmp($a->Key, $b->Key) < 0 ? -1 : 1;
      }

      return $a->Rating - $b->Rating < 0 ? -1 : 1;
    }
    
    public function getTerm() {
      return $this->_term;
    }
    
    public function getTranslations() {
      return $this->_translations;
    }
    
    public function getRevisions() {
      return $this->_revisions;
    }
    
    public function getLoggedIn() {
      return $this->_loggedIn;
    }
    
    public function getLanguages() {
      return $this->_languages;
    }
    
    public function getTypes() {
      return $this->_types;
    }
    
    public function getWordExists() {
      return $this->_wordExists;
    } 
    
    public function getNamespaces() {
      return $this->_namespaces;
    }
    
    public function getIndexes() {
      return $this->_keywordIndexes;
    }
    
    public function getAccountID() {
      return Session::getAccountID();
    }
  }
?>
