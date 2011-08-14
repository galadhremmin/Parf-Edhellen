<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class NamespaceService extends RESTfulService {
    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getNamespace');
      parent::registerMethod('save', 'saveNamespace');
    }
    
    public function handleRequest(&$data) {
      throw new ErrorException('Parameterless request presently unsupported.');
    }
    
    protected static function getNamespace($id) {    
      $namespace = new DictionaryNamespace();
      $namespace->load($id);
      
      if ($namespace->id < 1) {
        return null;
      }
      
      return $namespace;
    }
    
    protected static function saveNamespace(&$data) {
      if (!isset($data['identifier'])) {
        throw new ErrorException("Missing parameter 'identifier'.");
      }

      $data = array(
        'identifier' => preg_replace('/<|>/', '', $data['identifier'])
      );
      
      $namespaceObj = new DictionaryNamespace($data);
      $namespaceObj->save();
      
      return $namespaceObj;
    }
  }
?>