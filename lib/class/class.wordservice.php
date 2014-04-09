<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class WordService extends ServiceBase {
    const CACHE_LIFESPAN_MINUTES = 43200; // cache for a month (60 * 24 * 30)

    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getWord');
      parent::registerMethod('search', 'searchWord');
    }
  
    public function handleRequest(&$data) {
      throw new ErrorException('Parameterless request presently unsupported.');
    }
    
    protected static function getWord($id) {
      $word = new Word();
      $word->load($id);
      
      if ($word->id < 1) {
        return null;
      }
      
      return $word;
    }
    
    protected static function searchWord(&$input) {
      if (!isset($input['term'])) {
        throw new ErrorException("Missing parameter 'term'.");
      }
      
      if (!isset($input['language-filter'])) {
        $input['language-filter'] = 0; // default: disabled
      }
      
      $term = (string) $input['term'];
      $filter = (string) $input['language-filter'];
      $preciseness = 0;
      $data = array('cached' => true, 'words' => array(), 'cap' => 0);
      
      // Only measure characters that might contribute to the exactness
      // of the query. Multiply with 100 to signify the greater result set
      // the preciser the query is.
      $preciseness = strlen(preg_replace('/[%\\*\\s\\.\\/\\\\]/', '', $term)) * WORD_SERVICE_PRECISENESS_STEPSIZE;
      
      if ($preciseness > 0) {
        $executionStart = microtime(true);
        if (Session::isValid() || !self::populateFromCache($input, $data)) {
          self::populateFromDatabase($input, $preciseness, $data);
          $data['cached'] = false;
        }
        $data['cap'] = $preciseness;
        $data['time'] = (microtime(true) - $executionStart) * 1000;
      }
      
      return $data;
    }
    
    private static function populateFromDatabase(array &$input, $limitResult, array& $data) {
      $db = Database::instance();
      
      $term   = trim($input['term']);
      $filter = $input['language-filter'];
      
      if (strlen($term) < 1) {
        return;
      }
      
      $term = StringWizard::normalize($term);
      
      if (strpos($term, '*') !== false) {
        $term = str_replace('*', '%', $term);
      } else {
        $term .= '%';
      }
      
      if ($filter > 0) {
        $query = $db->connection()->prepare(
          "SELECT DISTINCT w.`Key`, w.`NormalizedKey`
            FROM `translation` t 
              INNER JOIN `word` w ON w.`KeyID` = t.`WordID`
            WHERE t.`Latest` = 1 AND t.`LanguageID` = ? AND w.`NormalizedKey` LIKE ?
            ORDER BY w.`Key` ASC"
        );
        
        $query->bind_param('is', $filter, $term);
      } else {
        $query = $db->connection()->prepare(
          "SELECT DISTINCT k.`Keyword`, k.`NormalizedKeyword`
             FROM `keywords` k
             WHERE k.`NormalizedKeyword` LIKE ?
             ORDER BY k.`NormalizedKeyword` ASC"
          /*
          "SELECT DISTINCT w.`Key`
            FROM `word` w
            WHERE w.`NormalizedKey` LIKE ? AND (
              EXISTS(SELECT NULL FROM `translation` t WHERE t.`Latest` = 1 AND t.`WordID` = w.`KeyID`) OR
              EXISTS(SELECT NULL FROM `namespace` n 
                INNER JOIN `translation` t2 ON t2.`NamespaceID` = n.`NamespaceID` AND t2.`Latest` = 1
                WHERE n.`IdentifierID` = w.`KeyID`)
            )
            ORDER BY w.`Key` ASC"
          */
        );
        $query->bind_param('s', $term);
      }
      
      $query->execute();
      
      $query->bind_result($key, $nkey);
      
      $index = 0;
      while ($query->fetch()) {
        if ($index < $limitResult) {
          $data['words'][] = array('key' => $key, 'nkey' => $nkey);
        }
        ++$index;
      }
      
      $data['matches'] = $index;
      
      $query->close();
      
      if ($data['matches'] > 0) {
        $cache = new DatabaseCache($db, self::CACHE_LIFESPAN_MINUTES, self::getCacheName($input));
        $cache->save(json_encode(array($data['words'], $data['matches'])));
      }
    }
    
    private static function populateFromCache(array &$input, array& $data) {
      $file = self::getCacheName($input);
      $db = Database::instance();
      $cache = new DatabaseCache($db, self::CACHE_LIFESPAN_MINUTES, $file); 
      
      if ($cache->hasExpired()) { // hourly refresh rate
        return false;
      }
      
      $contents = $cache->load();
 
      if (strlen($contents) < 1) {
        return false;
      }
      
      $d = @json_decode($contents);
      
      if ($d != null) {
        $data['words'] = $d[0];
        $data['matches'] = $d[1];
        $data['cache-age'] = -1;
        
        return true;
      }
      
      return false;
    }
    
    private static function getCacheName(array &$data) {
      return StringWizard::normalize($data['term']);  
    }
  }
?>
