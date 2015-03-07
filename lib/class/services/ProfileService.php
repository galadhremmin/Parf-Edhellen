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
      $credentials =& \auth\Credentials::request(new BasicAccessRequest());
      
      $author = $credentials->author();
      $author->nickname = $data['nickname'];
      $author->complete();
      
      return true;
    }
    
    protected static function editProfile(&$data) {
      $credentials =& \auth\Credentials::request(new BasicAccessRequest());
      $account     =& $credentials->account();
      
      if (isset($data['profile'])) {
        $account->profile = $data['profile'];
      }
      
      if (isset($data['tengwar'])) {
        $account->tengwar = $data['tengwar'];
      }
      
      $account->save();
      return $account;
    }
  }
