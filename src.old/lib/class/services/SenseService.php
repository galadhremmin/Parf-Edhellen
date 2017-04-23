<?php
  namespace services;
  
  class SenseService extends ServiceBase {
    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getSense');
    }
    
    public function handleRequest(&$data) {
      throw new \ErrorException('Parameterless request presently unsupported.');
    }
    
    protected static function getSense($id) {    
      $sense = new \data\entities\Sense();
      $sense->load($id);
      
      if ($sense->id < 1) {
        return null;
      }
      
      return $sense;
    }
  }
