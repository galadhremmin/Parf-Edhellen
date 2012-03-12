<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class WordService extends RESTfulService {
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
      $data = array('words' => array(), 'cap' => 0);
      
      // Only measure characters that might contribute to the exactness
      // of the query. Multiply with 100 to signify the greater result set
      // the preciser the query is.
      $preciseness = strlen(preg_replace('/[%\\*\\s\\.\\/\\\\]/', '', $term)) * 100;
      
      if ($preciseness > 0) {
        if (!self::populateFromCache($input, $data)) {
          self::populateFromDatabase($input, $preciseness, $data);
        }
        $data['cap'] = $preciseness;
      }
      
      return $data;
    }
    
    private static function populateFromDatabase(array &$input, $limitResult, array& $data) {
      $db = Database::instance();
      
      $term   = $input['term'];
      $filter = $input['language-filter'];
      
      if (strpos($term, '*') !== false) {
        $term = str_replace('*', '%', $term);
      } else {
        $term .= '%';
      }
      
      if ($filter > 0) {
        $query = $db->connection()->prepare(
          "SELECT DISTINCT w.`Key`
            FROM `translation` t 
              INNER JOIN `word` w ON w.`KeyID` = t.`WordID`
            WHERE t.`LanguageID` = ? AND w.`Key` LIKE ?
            ORDER BY w.`Key` ASC"
        );
        
        $query->bind_param('is', $filter, $term);
      } else {
        $query = $db->connection()->prepare(
          "SELECT DISTINCT `Key`
            FROM `word` w
            WHERE `Key` LIKE ?
            ORDER BY `Key` ASC"
        );
        $query->bind_param('s', $term);
      }
      
      $query->execute();
      
      $query->bind_result($key);
      
      $index = 0;
      while ($query->fetch()) {
        if ($index < $limitResult) {
          $data['words'][] = $key;
        }
        ++$index;
      }
      
      $data['matches'] = $index;
      
      $query->close();
      
      if ($data['matches'] > 0) {
        $fp = fopen(self::getCacheName($input), 'w');
        if (flock($fp, LOCK_EX)) {
          fwrite($fp, json_encode(array($data['words'], $data['matches'])));
          flock($fp, LOCK_UN);
        }
        fclose($fp);
      }
    }
    
    private static function populateFromCache(array &$input, array& $data) {
      $file = self::getCacheName($input);
      
      if (!file_exists($file)) {
        return false;
      }
      
      $d = @json_decode(file_get_contents($term));
      
      if ($d != null) {
        $data['data'] = $d[0];
        $data['matches'] = $d[1];
        
        return true;
      }
      
      return false;
    }
    
    private static function getCacheName(array &$data) {
      // This is necessary for the iconv-normalization to function properly
      setlocale(LC_ALL, 'de_DE.UTF8');
      
      // TODO: Implement this normalization procedure to all product searches
      return ROOT.'/cache/search-terms/'.$data['language-filter'].'-'.iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $data['term']);
    }
  }
?>