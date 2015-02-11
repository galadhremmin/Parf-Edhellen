<?php
  namespace models;
  
  class SentenceModel {
    private $_sentences;
    
    public function __construct() {
      $db = \data\Database::instance();
      
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
          $sentence = new \data\entities\Sentence($row->SentenceID, $row->Language, $row->Description, $row->Source);
          $this->_sentences[$row->SentenceID] = $sentence;
        }
        
        $this->_sentences[$row->SentenceID]->fragments[] = new \data\entities\SentenceFragment($row->FragmentID, $row->Fragment, $row->Tengwar, $row->TranslationID, $row->Comments);
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
  
