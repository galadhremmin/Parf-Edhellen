<?php
  class NamespaceService extends RESTfulService {
    public function handleRequest(&$data) {
      throw new ErrorException('Parameterless request presently unsupported.');
    } 
    
    public function handleParameterizedRequest(&$data, $param = null) {
      if (is_numeric($param)) {
        return self::getNamespace($param);
      }
      
      switch ($param) {
        case 'save':
          return self::saveNamespace($data);
          
        default:
          throw new ErrorException("Unrecognised command '".$param."'.");
      }
    }
    
    private function getNamespace($id) {    
      $namespace = new DictionaryNamespace();
      $namespace->load($id);
      
      if ($namespace->id < 1) {
        return null;
      }
      
      return $namespace;
    }
    
    private function saveNamespace(&$data) {
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