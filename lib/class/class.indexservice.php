<?php
  class IndexService extends RESTfulService {
    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getIndex');
      parent::registerMethod('save', 'saveIndex');
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
      $params = array(
        'namespaceID' => '/^[0-9]+$/',
        'indexWord' => ''
      );
    
      return null;
    }
  }
?>