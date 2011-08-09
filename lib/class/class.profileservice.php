<?php
  class ProfileService extends RESTfulService {
    public function handleRequest(&$data) {
      throw new ErrorException('Parameterless request presently unsupported.');
    } 
    
    public function handleParameterizedRequest(&$data, $param = null) {
      if (is_numeric($param)) {
        return self::getProfile($param);
      }
      
      switch ($param) {
        case 'edit':
          return self::editProfile($data);
          break;
        default:
          throw new ErrorException("Unrecognised command '".$param."'.");
      }
    }
    
    private function getProfile($id) {
      $author = new Author();
      $author->load($id);
      
      return $author;
    }
    
    private function editProfile(&$data) {
      if (!Session::isValid()) {
        throw new ErrorException('Insufficient privileges. Please authenticate.');
      }
    
      $author = new Author($data);
      $author->id = Session::getAccountID();
      
      return $author->save();
    }
  }
?>