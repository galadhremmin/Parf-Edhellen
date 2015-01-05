<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageSentenceModel {
    private $_sentences;
    
    public function __construct() {
      $db = Database::instance();
      
      $query = $db->connection()->query(
        'SELECT s.`SentenceID`, l.`Name` AS `Language`, s.`Description`, f.`Fragment`, f.`Tengwar`, f.`TranslationID`, f.`FragmentID`, f.`Comments`, s.`Source`
         FROM `sentence` s
           INNER JOIN `language` l ON l.`ID` = s.`LanguageID`
           INNER JOIN `sentence_fragment` f ON f.`SentenceID` = s.`SentenceID`
         ORDER BY f.`Order` ASC'
      );
          
      $this->_sentences = array();
      while ($row = $query->fetch_object()) {
        
        // Create a sentence if it hasn't previously been recorded.
        if (!isset($this->_sentences[$row->SentenceID])) {
          $sentence = new Sentence($row->SentenceID, $row->Language, $row->Description, $row->Source);
          $this->_sentences[$row->SentenceID] = $sentence;
        }
        
        $this->_sentences[$row->SentenceID]->fragments[] = new SentenceFragment($row->FragmentID, $row->Fragment, $row->Tengwar, $row->TranslationID, $row->Comments);
      }
      
      // Coalesce all fragments into sentences
      foreach ($this->_sentences as $sentence) {
        $sentence->create();
      }
      
      $query->close();
    }
    
    public function getSentences() {
      return $this->_sentences;
    }
  }
  
  class Sentence  {
    public $ID;
    public $language;
    public $fragments;
    public $sentence;
    public $sentenceTengwar;
    public $description;
    public $source;
    
    public function __construct($id, $language, $description, $source) {
      $this->ID = $id;
      $this->language = $language;
      $this->fragments = array();
      $this->description = $description;
      $this->sentence = '';
      $this->source = $source;
    }
    
    public function create() {
      $fragments = array();
      $fragmentsTengwar = array();
      $previousFragment = null;
      
      foreach ($this->fragments as $fragment) {
        if (!preg_match('/^[,\\.!\\s]$/', $fragment->fragment)) {
          if (count($fragments) > 0) {
            $fragments[] = ' ';
          }
          
          if (!is_null($fragment->tengwar) && count($fragmentsTengwar) > 0) {
            $fragmentsTengwar[] = ' ';
          }
        }
        
        if (is_numeric($fragment->translationID)) {
          $html = '<a href="#" id="ed-fragment-'.$fragment->fragmentID.
            '" data-fragment-id="'.$fragment->fragmentID.
            '" data-translation-id="'.$fragment->translationID.
            '">'.$fragment->fragment.'</a>';
          
            if ($previousFragment !== null) {
              $previousFragment->nextFragmentID = $fragment->fragmentID;
              $fragment->previousFragmentID = $previousFragment->fragmentID;
            }

            $previousFragment = $fragment;
        } else {        
          $html = $fragment->fragment;
        }
        
        $fragments[] = $html;
        
        if (!is_null($fragment->tengwar)) {
          $fragmentsTengwar[] = $fragment->tengwar;
        }
      }
      
      $this->sentence = implode($fragments);
      $this->sentenceTengwar = implode($fragmentsTengwar);
    }
  }
  
  class SentenceFragment {
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
      $this->comments = StringWizard::createLinks($comments);
      $this->previousFragmentID = 0;
      $this->nextFragmentID = 0;
      $this->tengwar = $tengwar;
    }
  }
?>
