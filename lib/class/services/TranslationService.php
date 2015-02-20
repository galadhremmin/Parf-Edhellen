<?php
  namespace services;
  
  class TranslationService extends ServiceBase {
    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getTranslation');
      parent::registerMethod('register', 'registerTranslation');
      parent::registerMethod('translate', 'translate');
    }
    
    public function handleRequest(&$data) {
      throw new \ErrorException('Parameterless request presently unsupported.');
    }
    
    protected static function translate(&$input) {
      if (!isset($input['term'])) {
        throw new Exception("Missing parameter 'term'.");
      }
      
      return \data\entities\Translation::translate($input['term'], null);
    }
    
    protected static function getTranslation($id) {
      $t = new \data\entities\Translation();
      $t->load($id);
      $t->transformContent();
      
      if ($t->index) {
        $t = null;
      }
      
      return $t;
    }
    
    protected static function registerTranslation(&$data) {
      $values = array(
        'type'        => array_keys(Translation::getTypes()),
        'senseID'     => '/^[0-9]+$/',
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
            throw new Exception('Malformed parameter: '.$key.'. Received "'.$value.'", expected according to format '.$validation);
          }
        }
      
        $values[$key] = stripslashes($value);
      }
    
      $translationObj = new \data\entities\Translation($values);
      return \data\entities\Word::registerTranslation($translationObj);
    }
  }
