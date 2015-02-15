<?php
  namespace services;
  
  class ProfileService extends ServiceBase {
    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getProfile');
      parent::registerMethod('complete', 'completeProfile');
      parent::registerMethod('edit', 'editProfile');
    }
    
    public function handleRequest(&$data) {
      throw new \ErrorException('Parameterless request presently unsupported.');
    }
    
    protected static function getProfile($id) {
      $author = new \data\entities\Author();
      $author->load($id);
      
      return $author;
    }
    
    protected static function completeProfile(&$data) {
      \auth\Session::completeRegistration($data['nickname']);
      return true;
    }
    
    protected static function editProfile(&$data) {
      if (!\auth\Session::isValid()) {
        throw new \ErrorException('Insufficient privileges. Please authenticate.');
      }
    
      $author = new \entities\Author($data);
      $author->id = \auth\Session::getAccount()->id;
      
      return $author->save();
    }
  }
