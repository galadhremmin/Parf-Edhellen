<?php
  namespace services;
  
  class IndexService extends ServiceBase {
    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getIndex');
      parent::registerMethod('save', 'saveIndex');
      parent::registerMethod('remove', 'removeIndex');
    }
    
    public function handleRequest(&$data) {
      throw new \ErrorException('Parameterless request presently unsupported.');
    }
    
    protected static function getIndex($id) {
      $index = new ent::Translation();
      $index->load($id);
      
      if (!$index->index) {
        $index = null;
      }
      
      return $index;
    }
    
    protected static function saveIndex($data) {
      $credentials =& \auth\Credentials::request(new \auth\BasicAccessRequest());
      $account =& $credentials->account();
      
      if (!isset($data['senseID']))
        throw new \exceptions\MissingParameterException('senseID');
        
      if (!isset($data['word']))
        throw new \exceptions\MissingParameterException('word');
    
      if (!is_numeric($data['senseID']))
        throw new \exceptions\InvalidParameterException('senseID');
      
      if (preg_match('/^[\\s]*$/', $data['word']))
        throw new \exceptions\InvalidParameterException('word');
      
      $data['authorID'] = $account->id;
      
      $t = new \data\entities\Translation($data);
      \data\entities\Word::registerIndex($t);
      
      $sense = new \data\entities\Sense();
      return $sense->load($t->senseID);
    }
    
    protected static function removeIndex($data) {
      \auth\Credentials::request(new \auth\BasicAccessRequest());
    
      if (!isset($data['id']))
        throw new MissingParameterException('id');
      
      if (!is_numeric($data['id']))
        throw new InvalidParameterException('id');
      
      $t = new \data\entities\Translation($data);
      $t->load();
      
      if (!$t->index) {
        throw new \exceptions\InvalidParameterException('id');
      }
      
      $t->remove();
      
      \data\entities\Word::unregisterReference($t->wordID);
      
      return $t;
    }
  }
