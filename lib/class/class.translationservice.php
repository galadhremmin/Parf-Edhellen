<?php
  class TranslationService extends RESTfulService {
    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getTranslation');
      parent::registerMethod('register', 'registerTranslation');
    }
    
    public function handleRequest(&$data) {
      throw new ErrorException('Parameterless request presently unsupported.');
    }
    
    protected static function getTranslation($id) {
      $t = new Translation();
      $t->load($id);
      
      if ($t->index) {
        $t = null;
      }
      
      return $t;
    }
    
    protected static function registerTranslation(&$data) {
      $values = array(
        'type'        => array_keys(Translation::getTypes()),
        'namespaceID' => '/^[0-9]+$/',
        'id'          => '/^[0-9]+$/',
        'language'    => '/^[0-9]+$/',
        'word'        => null,
        'translation' => null,
        'etymology'   => null,
        'source'      => null,
        'comments'    => null,
        'tengwar'     => null,
        'phonetic'    => null
      );
     
      foreach ($values as $key => $validation) {
        if (!isset($data[$key])) {
          throw new Exception('Missing parameter: '.$key);
        }
      
        $value = $data[$key];
      
        if ($validation !== null) {
          if ((is_array($validation) && !in_array($value, $validation)) ||
              (is_string($validation) && !preg_match($validation, $value))) {
            throw new Exception('Malformed parameter: '.$key);
          }
        }
      
        $values[$key] = stripslashes($value);
      }
    
      $translationObj = new Translation($values);
      return Word::registerTranslation($translationObj);
    }
  }
?>