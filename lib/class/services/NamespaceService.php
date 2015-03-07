<?php
  namespace services;
  
  class SenseService extends ServiceBase {
    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getSense');
      parent::registerMethod('save', 'saveSense');
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
    
    protected static function saveSense(&$data) {
      \auth\Credentials::request(new \auth\BasicAccessRequest());
    
      if (!isset($data['identifier'])) {
        throw new \ErrorException("Missing parameter 'identifier'.");
      }

      $data = array(
        'identifier' => preg_replace('/<|>/', '', $data['identifier'])
      );
      
      $sense = new \data\entities\Sense($data);
      $sense->save();
      
      return $sense;
    }
  }
