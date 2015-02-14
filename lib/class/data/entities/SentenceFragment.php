<?php
  namespace data\entities;
  
  class SentenceFragment extends Entity {
    public $fragmentID;
    public $translationID;
    public $fragment;
    public $comments;
    public $previousFragmentID;
    public $nextFragmentID;
    public $tengwar;
    
    public function __construct($fragmentID, $fragment, $tengwar, $translationID, $comments) {
      $this->fragmentID = $fragmentID;
      $this->fragment = $fragment;
      $this->translationID = $translationID;
      $this->comments = \utils\StringWizard::createLinks($comments);
      $this->previousFragmentID = 0;
      $this->nextFragmentID = 0;
      $this->tengwar = $tengwar;
    }
    
    public function validate() {
    }
    
    public function load($numericId) {
    }
    
    public function save() {
    }
  }
