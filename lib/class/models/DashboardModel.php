<?php
  namespace models;
  use \data\entities;
  
  class DashboardModel {
    private $_translations;
    private $_favourites;
    private $_reviews;
    
    public function __construct() {
      $account =& \auth\Credentials::current()->account();
      
      $this->_translations = entities\Translation::getByAccount($account);
      $this->_favourites   = entities\Favourite::getByAccount($account);
      $this->_reviews      = null;

      if ($account->isAdministrator()) {
        $this->_reviews = entities\TranslationReview::getPendingReviews();
      }
    }
    
    public function getTranslations() {
      return $this->_translations;
    } 
    
    public function getFavourites() {
      return $this->_favourites;
    }

    public function getReviews() {
      return $this->_reviews;
    }
  }
?>
