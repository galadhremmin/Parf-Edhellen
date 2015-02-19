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
      \auth\Session::canWriteSelf();
    
      $account = \auth\Session::getAccount();
      
      if (isset($data['profile'])) {
        $account->profile = $data['profile'];
      }
      
      if (isset($data['tengwar'])) {
        $account->tengwar = $data['tengwar'];
      }
      
      $account->save();
    }
  }
