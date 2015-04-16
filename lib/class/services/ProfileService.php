<?php
  namespace services;
  
  class ProfileService extends ServiceBase {
    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getProfile');
      parent::registerMethod('complete', 'completeProfile');
      parent::registerMethod('edit', 'editProfile');
      parent::registerMethod('favourite', 'favourite');
    }
    
    public function handleRequest(&$data) {
      throw new \ErrorException('Parameterless request presently unsupported.');
    }
    
    protected static function getProfile($id) {
      $credentials =& \auth\Credentials::request(new \auth\BasicAccessRequest());
      return $credentials->account();
    }
    
    protected static function completeProfile(array &$data) {
      $credentials =& \auth\Credentials::request(new \auth\BasicAccessRequest());
      
      $author = $credentials->account();
      $author->nickname = $data['nickname'];
      $author->complete();
      
      return true;
    }
    
    protected static function editProfile(array &$data) {
      $credentials =& \auth\Credentials::request(new \auth\BasicAccessRequest());
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
    
    protected static function favourite(array &$data) {
      $credentials =& \auth\Credentials::request(new \auth\BasicAccessRequest());
      
      if (!isset($data['translationID'])) {
        throw new \exceptions\MissingParameterException('translationID');
      }
      
      if (!isset($data['add'])) {
        throw new \exceptions\MissingParameterException('add');
      }

      if (!is_numeric($data['translationID'])) {
        throw new \exceptions\InvalidParameterException('translationID');
      }

      $id  = intval($data['translationID']);
      $add = boolval($data['add']);
      
      $translation = new \data\entities\Translation();
      $translation->load($id);
      
      if ($translation->validate()) {
        $favourite = new \data\entities\Favourite(array(
          'translation' => $translation,
          'accountID'   => $credentials->account()->id
        ));
        
        $favourite->save();
      }
      
      return $id;
    }
  }
  
