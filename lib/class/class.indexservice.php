<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class IndexService extends RESTfulService {
    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getIndex');
      parent::registerMethod('save', 'saveIndex');
      parent::registerMethod('remove', 'removeIndex');
    }
    
    public function handleRequest(&$data) {
      throw new ErrorException('Parameterless request presently unsupported.');
    }
    
    protected static function getIndex($id) {
      $index = new Translation();
      $index->load($id);
      
      if (!$index->index) {
        $index = null;
      }
      
      return $index;
    }
    
    protected static function saveIndex($data) {
      if (!isset($data['namespaceID']))
        throw new MissingParameterException('namespaceID');
        
      if (!isset($data['word']))
        throw new MissingParameterException('word');
    
      if (!is_numeric($data['namespaceID']))
        throw new InvalidParameterException('namespaceID');
      
      if (preg_match('/^[\\s]*$/', $data['word']))
        throw new InvalidParameterException('word');
      
      $t = new Translation($data);
      Word::registerIndex($t);
      
      $namespace = new DictionaryNamespace();
      return $namespace->load($t->namespaceID);
    }
    
    protected static function removeIndex($data) {
      if (!isset($data['id']))
        throw new MissingParameterException('id');
      
      if (!is_numeric($data['id']))
        throw new InvalidParameterException('id');
      
      $t = new Translation($data);
      $t->load();
      
      if (!$t->index) {
        throw new InvalidParameterException('id');
      }
      
      $t->remove();
      
      Word::unregisterReference($t->wordID);
      
      return $t;
    }
  }
?>