<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class Translation {
  
    // Mutable columns
    public $word;
    public $translation;
    public $etymology;
    public $type;
    public $source;
    public $comments;
    public $tengwar;
    public $gender;
    public $phonetic;
    public $language;
    public $namespaceID;
    public $owner;
    
    // Semi-mutable column
    public $index;
    public $rating;
    
    // Read-only columns
    public $id;
    public $wordID;
    public $dateCreated;
    public $authorID;
    public $authorName;
    public $latest;

    // Static comntainer for type lists. This is meant to speed up bulk loading by reducing
    // database access.
    private static $availableTypes = null;
  
    public function __construct($data = null) {
      
      if ($data !== null && is_array($data)) {
        $fields = get_object_vars($this);
        
        foreach ($fields as $field => $type) {
          if (isset($data[$field])) {
            $this->$field = $data[$field];
          }
        }
      }
    }
    
    public function validate() {
      if (preg_match('/^\\s*$/', $this->word) || 
          preg_match('/^\\s*$/', $this->translation) || 
          (!$this->index && $this->language < 1)) {
        return false;
      }
      
      return true;
    }
    
    public function remove() {
      throw new Exception('Not implemented exception');
    
      if ($this->id < 1) {
        throw new InvalidParameterException('id');
      }
      
      $conn = Database::instance()->exclusiveConnection();
      
      // TODO: Deassociate all words from the translation entry
     
      $stmt = $conn->prepare('DELETE FROM `keywords` WHERE `TranslationID` = ?');
      $stmt->bind_param('i', $this->id);
      $stmt->execute();
      
      $stmt = $conn->prepare('DELETE FROM `translation` WHERE `TranslationID` = ?');
      $stmt->bind_param('i', $this->id);
      $stmt->execute();
    }
    
    public function load($id = null) {
      // result container
      if ($id === null) {
        $id = $this->id;
      }
      
      if ($id < 1) {
        throw new InvalidParameterException('id');
      }
      
      $db = Database::instance();
      $query = $db->connection()->prepare(
        'SELECT 
          t.`LanguageID`, t.`Translation`, t.`Etymology`, t.`Type`, t.`Source`, t.`Comments`, 
          t.`Tengwar`, t.`Gender`, t.`Phonetic`, w.`Key`, t.`NamespaceID`, t.`AuthorID`,
          t.`DateCreated`, t.`Latest`, t.`Index`, t.`WordID`, t.`EnforcedOwner`
         FROM `translation` t 
         LEFT JOIN `word` w ON w.`KeyID` = t.`WordID`
         WHERE t.`TranslationID` = ?'
      );

      $query->bind_param('i', $id);
      $query->execute();
      $query->bind_result(
        $this->language, $this->translation, $this->etymology, $this->type, $this->source, $this->comments,
        $this->tengwar, $this->gender, $this->phonetic, $this->word, $this->namespaceID, $this->authorID,
        $this->dateCreated, $this->latest, $this->index, $this->wordID, $this->owner
      );
      
      if ($query->fetch()) {
        $this->id = $id;
      }
      
      $query->close(); 
    }
    
    public static function getTypes() {
      if (is_array(self::$availableTypes)) {
        return self::$availableTypes;
      }
    
      $db = Database::instance();
    
      $data = array();
      $query = $db->connection()->query(
        "SHOW COLUMNS FROM `translation` WHERE `Field` = 'Type'"
      );
      
      while ($row = $query->fetch_object()) {
        $values = null;
        if (preg_match_all('/\'([a-zA-Z\\/\\|]+)\'/', $row->Type, $values)) {
          foreach ($values[1] as $value) {
            $data[$value] = str_replace(array('/', '|'), array('. and ', '. or '), $value).'.';
          }
          
          ksort($data);
        }
      }
      
      $query->close();

      // Save the results, for quicker access next time.
      self::$availableTypes = $data;
      return $data;
    }
    
    public static function translate($term, $languageFilter = null) {
      $db             = Database::instance();
      $normalizedTerm = StringWizard::normalize($term);
    
      $data         = array();
      $namespaceIDs = array();
      
      // Attempt to find the namespaces associated with the word. This might yield multiple
      // IDs, so these will be put in an array.
      $query = $db->connection()->prepare(
        'SELECT DISTINCT k.`NamespaceID`, k.`Keyword`
           FROM `keywords` k
           WHERE k.`NormalizedKeyword` = ? AND k.`NamespaceID` IS NOT NULL
           UNION (
            SELECT t.`NamespaceID` , k.`Keyword`
              FROM `keywords` k
                INNER JOIN `translation` t ON t.`TranslationID` = k.`TranslationID`
              WHERE k.`TranslationID` IS NOT NULL AND k.`NormalizedKeyword` = ?
          )'
      );
      
      $query->bind_param('ss', $normalizedTerm, $normalizedTerm);
      $query->execute();
      $query->bind_result($namespaceID, $identifier);
      
      $data['namespaces'] = array();
      while ($query->fetch()) {
        $namespaceIDs[] = $namespaceID;
        $data['namespaces'][$namespaceID] = $identifier;
      }
      
      $query->close();
      
      if (count($namespaceIDs) < 1) {
        return null;
      }
      
      $namespaceIDs = implode(',', $namespaceIDs);

      // Find all translations for the words specified. The array of IDs is used
      // now as a means to identify the words themselves.
      $query = $db->connection()->prepare(
        'SELECT w.`Key` AS `Word`, t.`TranslationID`, t.`Translation`, t.`Etymology`, 
           t.`Type`, t.`Source`, t.`Comments`, t.`Tengwar`, t.`Phonetic`,
           l.`Name` AS `Language`, t.`NamespaceID`, l.`Invented` AS `LanguageInvented`,
           t.`EnforcedOwner`, t.`AuthorID`, a.`Nickname`, w.`NormalizedKey`
         FROM `translation` t
         INNER JOIN `word` w ON w.`KeyID` = t.`WordID`
         INNER JOIN `language` l ON l.`ID` = t.`LanguageID`
         LEFT JOIN `auth_accounts` a ON a.`AccountID` = t.`AuthorID`
         WHERE t.`NamespaceID` IN('.$namespaceIDs.') AND t.`Latest` = 1
         ORDER BY t.`NamespaceID` ASC, l.`Name` DESC, w.`Key` ASC'
      );
      
      $query->execute();
      $query->bind_result(
        $word, $translationID, $translation, $etymology, $type, 
        $source, $comments, $tengwar, $phonetic, $language, 
        $namespaceID, $inventedLanguage, $owner, $authorID, 
        $authorName, $normalizedWord
      );
      
      $data['translations']   = array();
      $data['keywordIndexes'] = array();
      
      while ($query->fetch()) {
      
        if (!$inventedLanguage) {
          
          $ptr =& $data['keywordIndexes'];
        
        } else {
          
          if (!isset($data['translations'][$language]))
            $data['translations'][$language] = array();
          
          $ptr =& $data['translations'][$language];
        }
        
        // Order affected associative array by language
        $translation = new Translation(
          array(
            'word'        => $word,
            'id'          => $translationID,
            'translation' => StringWizard::createLinks($translation),
            'etymology'   => StringWizard::createLinks($etymology),
            'type'        => $type,
            'tengwar'     => StringWizard::preventXSS($tengwar),
            'phonetic'    => StringWizard::preventXSS($phonetic),
            'source'      => StringWizard::preventXSS($source),
            'comments'    => empty($comments) ? null : StringWizard::createLinks($comments),
            'language'    => $language,
            'namespaceID' => $namespaceID,
            'owner'       => $owner,
            'authorID'    => $authorID,
            'authorName'  => $authorName
          )
        );
        
        self::calculateRating($translation, $normalizedTerm);
        
        $ptr[] = $translation;
      }
      
      $query->close();
      
      foreach (array_keys($data['translations']) as $language)
        usort($data['translations'][$language], 'TranslationComparer::compare');

      return $data;
    }
    
    private static function calculateRating(Translation & $translation, $term) {
      $rating = 0;
      
      // First, check if the gloss contains the search term by looking for its
      // position within the word property, albeit normalized.
      $n = StringWizard::normalize($translation->word);
      $pos = strpos($n, $term);
      
      if ($pos !== false) {
        // The "cleaner" the match, the better
        $rating = 100000 + ($pos * -1) * 10;
        
        if ($pos === 0 && $n == $term) {
          $rating *= 2;
        }
      }
      
      // If the previous check failed, check for the translations field. Statistically,
      // this is the most common case.
      if ($rating === 0) {
        $n = StringWizard::normalize($translation->translation);
        $pos = strpos($n, $term);
        
        if ($pos !== false) {
          $rating = 10000 + ($pos * -1) * 10;
          
          if ($pos === 0 && $n == $term) {
            $rating *= 2;
          }
        }
      }
      
      // If the previous check failed, check within the comments field. Statistically,
      // this is an uncommon match.
      if ($rating === 0 && $translation->comments !== null) {
        $n = StringWizard::normalize($translation->comments);
        $pos = strpos($n, $term);
        
        if ($pos !== false) {
          $rating = 1000;
        }
      }
      
      // Default rating for all other cases, probably matches by keyword.
      if ($rating === 0) {
        $rating = 100;
      }
      
      // Bump all unverified translations to a trailing position
      if ($translation->owner === 0) {
        $rating = -110000 + $rating;
      }
      
      $translation->rating = $rating;
    }
  }
?>
