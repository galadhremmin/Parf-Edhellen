<?php
  namespace services;
  
  class WordService extends ServiceBase {
    const CACHE_LIFESPAN_MINUTES = 480; // cache for eight hours (60 * 8)

    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getWord');
      parent::registerMethod('search', 'searchWord');
    }
  
    public function handleRequest(&$data) {
      throw new \ErrorException('Parameterless request presently unsupported.');
    }
    
    protected static function getWord($id) {
      $word = new \data\entities\Word();
      $word->load($id);
      
      if ($word->id < 1) {
        return null;
      }
      
      return $word;
    }
    
    protected static function searchWord(&$input) {
      if (!isset($input['term'])) {
        throw new \ErrorException("Missing parameter 'term'.");
      }
      
      if (!isset($input['language-filter'])) {
        $input['language-filter'] = 0; // default: disabled
      } else {
        settype($input['language-filter'], 'integer');
      }

      $term        = (string) $input['term'];
      $reversed    = false;
      $preciseness = 0;
      $data        = array('cached' => true, 'words' => array(), 'cap' => 0);
      
      // Only measure characters that might contribute to the exactness
      // of the query. Multiply with 100 to signify the greater result set
      // the preciser the query is.
      $preciseness = strlen(preg_replace('/[%\\*\\s\\.\\/\\\\]/', '', $term)) * WORD_SERVICE_PRECISENESS_STEPSIZE;
      
      if ($preciseness > 0) {
        $executionStart = microtime(true);
        if (\auth\Credentials::current()->account() !== null || !self::populateFromCache($input, $data)) {
          self::populateFromDatabase($input, $preciseness, $data);
          $data['cached'] = false;
        }
        $data['cap'] = $preciseness;
        $data['time'] = (microtime(true) - $executionStart) * 1000;
      }
      
      return $data;
    }
    
    private static function populateFromDatabase(array &$input, $limitResult, array& $data) {
      $db = \data\Database::instance();
      
      $term     = trim($input['term']);
      $filter   = (integer) $input['language-filter'];
      $reversed = false;
      
      if (isset($input['reversed'])) {
        $reversed = (boolean) $input['reversed'];
      }
      
      if (strlen($term) < 1) {
        return;
      }
      
      $term = \utils\StringWizard::normalize($term);
      
      if (strpos($term, '*') !== false) {
        $term = str_replace('*', '%', $term);
      } else {
        $term .= '%';
      }
      
      if ($filter > 0) {
        
        $column = 'NormalizedKey';
        if ($reversed) {
          $column = 'ReversedNormalizedKey';
        }
        
        $query = $db->connection()->prepare(
          "SELECT DISTINCT w.`Key`, w.`NormalizedKey`
            FROM `translation` t 
              INNER JOIN `word` w ON w.`KeyID` = t.`WordID`
            WHERE t.`Latest` = '1' AND t.`Deleted` = b'0' AND t.`LanguageID` = ? AND w.`".$column."` LIKE ?
            ORDER BY w.`Key` ASC"
        );
        
        $query->bind_param('is', $filter, $term);
      } else {
      
        $column = 'NormalizedKeyword';
        if ($reversed) {
          $column = 'ReversedNormalizedKeyword';
        }
      
        $query = $db->connection()->prepare(
          "SELECT DISTINCT k.`Keyword`, k.`NormalizedKeyword`
             FROM `keywords` k
             WHERE k.`".$column."` LIKE ?
             ORDER BY k.`NormalizedKeyword` ASC"
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
        $cache = new \data\DatabaseCache($db, self::CACHE_LIFESPAN_MINUTES, self::getCacheName($input));
        $cache->save(json_encode(array($data['words'], $data['matches'])));
      }
    }
    
    private static function populateFromCache(array &$input, array& $data) {
      $file = self::getCacheName($input);
      $db = \data\Database::instance();
      $cache = new \data\DatabaseCache($db, self::CACHE_LIFESPAN_MINUTES, $file); 
      
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
      $key = \utils\StringWizard::normalize($data['term']);
      
      if (isset($data['language-filter']) && !empty($data['language-filter'])) {
        $key .= '/'.$data['language-filter'];
      }
      
      if (isset($data['reversed']) && (boolean) $data['reversed']) {
        $key .= '/r';
      }
      
      return $key;
    }
  }
