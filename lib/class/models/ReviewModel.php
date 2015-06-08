<?php
  /**
 * Created by PhpStorm.
 * User: zanathel
 * Date: 6/8/15
 * Time: 8:42 PM
 */

  namespace models;


  class ReviewModel {
    private $_review;

    public function __construct() {
      $reviewID = 0;
      if (isset($_GET['reviewID']) && is_numeric($_GET['reviewID'])) {
        $reviewID = intval( $_GET['reviewID'] );
      }

      $review = new \data\entities\TranslationReview();
      $review->load($reviewID);
      $this->_review = $review;
    }

    public function getReview() {
      return $this->_review;
    }
  }