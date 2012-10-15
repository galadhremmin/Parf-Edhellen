<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class ProfileService extends ServiceBase {
    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getProfile');
      parent::registerMethod('edit', 'editProfile');
    }
    
    public function handleRequest(&$data) {
      throw new ErrorException('Parameterless request presently unsupported.');
    }
    
    protected static function getProfile($id) {
      $author = new Author();
      $author->load($id);
      
      return $author;
    }
    
    protected static function editProfile(&$data) {
      if (!Session::isValid()) {
        throw new ErrorException('Insufficient privileges. Please authenticate.');
      }
    
      $author = new Author($data);
      $author->id = Session::getAccountID();
      
      return $author->save();
    }
  }
?>