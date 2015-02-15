<?php
  namespace data\entities;
  
  class Sentence extends Entity {
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
    
    public function validate() {
    }
    
    public function load($numericId) {
    }
    
    public function save() {
    }
    
    public function create() {
      $fragments = array();
      $fragmentsTengwar = array();
      $previousFragment = null;
      
      foreach ($this->fragments as $fragment) {
        if (!preg_match('/^[,\\.!\\s\\?]$/', $fragment->fragment)) {
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
    
    public static function updateReference($id, Translation& $trans) {
      \auth\Session::canWriteSelf();
      
      $db = \data\Database::instance()->connection();

      $query = $db->prepare('UPDATE `sentence_fragment` SET `TranslationID` = ? WHERE `TranslationID` = ?');
      $query->bind_param('ii', $trans->id, $id);
      $query->execute();
      $query->close();
    }
  }
  
