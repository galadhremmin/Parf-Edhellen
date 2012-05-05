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
                
        // Load first data necessary for interaction 
        // Languages
        $data = array();
        $query = $db->connection()->query(
          'SELECT `ID`, `Name` FROM `language` WHERE `Invented` = 1 ORDER BY `Order` ASC'
        );
      
        while ($row = $query->fetch_object()) {
          $data[$row->ID] = $row->Name;
        }
      
        $query->close();
      
        $this->_languages = $data;
      
        // Grammar types
        $this->_types = Translation::getTypes();

        $data         = array();
        $namespaceIDs = array();
        
        // Attempt to find the word asked for. This might yield multiple
        // IDs, so these will be put in an array.
        $query = $db->connection()->prepare(
          'SELECT DISTINCT n.`NamespaceID`, wN.`Key`
              FROM `namespace` n
              LEFT JOIN `word` wN ON wN.`KeyID` = n.`IdentifierID`
              WHERE wN.`NormalizedKey` = ?
            UNION (
              SELECT DISTINCT t.`NamespaceID`, wN.`Key`
              FROM `translation` t
              LEFT JOIN `word` wT ON wT.`KeyID` = t.`WordID`
              LEFT JOIN `namespace` n ON n.`NamespaceID` = t.`NamespaceID`
              LEFT JOIN `word` wN ON wN.`KeyID` = n.`IdentifierID`
              WHERE wT.`NormalizedKey` = ?
            )'
        );
        
        $query->bind_param('ss', $this->_term, $this->_term);
        $query->execute();
        $query->bind_result($namespaceID, $identifier);
        
        $this->_namespaces = array();
        
        while ($query->fetch()) {
          $namespaceIDs[] = $namespaceID;
          
          $this->_namespaces[$namespaceID] = $identifier;
        }
        
        $query->close();
        
        // If the array is empty, make sure to inform the view that
        // the word asked for does not exist
        if (count($namespaceIDs) < 1) {
          return;
        }        
                
        $namespaceIDs = implode(',', $namespaceIDs);
          
        // Find all translations for the words specified. The array of IDs is used
        // now as a means to identify the words themselves.
        $query = $db->connection()->prepare(
          'SELECT w.`Key` AS `Word`, t.`TranslationID`, t.`Translation`, t.`Etymology`, 
             t.`Type`, t.`Source`, t.`Comments`, t.`Tengwar`, t.`Phonetic`,
             l.`Name` AS `Language`, t.`NamespaceID`, l.`Invented` AS `LanguageInvented`,
             t.`EnforcedOwner`
           FROM `translation` t
           LEFT JOIN `word` w ON w.`KeyID` = t.`WordID`
           LEFT JOIN `language` l ON l.`ID` = t.`LanguageID`
           WHERE t.`NamespaceID` IN('.$namespaceIDs.') AND t.`Latest` = 1 AND w.`Key` IS NOT NULL
           ORDER BY t.`NamespaceID` ASC, l.`Name` DESC, w.`Key` ASC'
        );
        
        $query->execute();
        $query->bind_result(
          $word, $translationID, $translation, $etymology, $type, 
          $source, $comments, $tengwar, $phonetic, $language,
          $namespaceID, $inventedLanguage, $owner
        );
        
        $this->_translations   = array();
        $this->_keywordIndexes = array();
        
        while ($query->fetch()) {
        
          if (!$inventedLanguage) {
            
            $ptr =& $this->_keywordIndexes;
          
          } else {
            
            if (!isset($this->_translations[$language]))
              $this->_translations[$language] = array();
            
            $ptr =& $this->_translations[$language];
          }
          
          // Order affected associative array by language
          $ptr[] = new Translation(
            array(
              'word'        => $word,
              'id'          => $translationID,
              'translation' => StringWizard::createLinks($translation),
              'etymology'   => StringWizard::createLinks($etymology),
              'type'        => $type,
              'tengwar'     => StringWizard::preventXSS($tengwar),
              'phonetic'    => StringWizard::preventXSS($phonetic),
              'source'      => StringWizard::preventXSS($source),
              'comments'    => StringWizard::createLinks($comments),
              'language'    => $language,
              'namespaceID' => $namespaceID,
              'owner'       => $owner
            )
          );
        }
        
        $query->close();
        
        $this->_wordExists = true;
        
        // Find all revisions
        $query = $db->connection()->query(
          "SELECT t.`TranslationID`, DATE_FORMAT(t.`DateCreated`, '%Y-%m-%d') AS `DateCreated`, 
             t.`Latest`, w.`Key`, a.`Nickname` AS `AuthorName`, a.`AccountID` AS `AuthorID`
           FROM `translation` t
           LEFT JOIN `word` w ON w.`KeyID` = t.`WordID`
           LEFT JOIN `auth_accounts` a ON a.`AccountID` = t.`AuthorID`
           WHERE t.`NamespaceID` IN(".$namespaceIDs.") AND t.`Index` = '0'
           ORDER BY w.`Key` ASC, t.`Latest` DESC, t.`DateCreated` DESC"
        );
      
        // reset the data array, as it will now contain a different
        // data set
        $data = array();
        while ($row = $query->fetch_object()) {
          $data[] = $row;
        }
      
        $query->close();
      
        $this->_revisions = $data;
      }
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