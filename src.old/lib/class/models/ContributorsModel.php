<?php
  namespace models;

  class ContributorsModel {
    private $_authors;
    private $_activeAuthors;
   
    public function __construct() {
      $this->_authors = array();
      $this->_activeAuthors = array();
    
      $db = \data\Database::instance();
      
      $res = $db->connection()->query(
        'SELECT `AccountID`,`Nickname`, `DateRegistered`, `Tengwar`, 
         (SELECT COUNT(`KeyID`) FROM `word` WHERE `AuthorID` = `AccountID`) AS `Words`,
         (SELECT COUNT(`TranslationID`) FROM `translation` WHERE `AuthorID` = `AccountID`) AS `Translations`
         FROM `auth_accounts` 
         ORDER BY `Nickname` ASC');
      
      if ($res !== null) {
        while ($row = $res->fetch_object()) {
          $author = new Author(array(
            'id'               => $row->AccountID,
            'nickname'         => $row->Nickname,
            'dateRegistered'   => $row->DateRegistered,
            'tengwar'          => $row->Tengwar,
            'wordCount'        => $row->Words,
            'translationCount' => $row->Translations
          ));
          
          $this->_authors[] = $author;          
          if ($row->Words > 0 || $row->Translations > 0) {
            $this->_activeAuthors[] = $author;
          }
        }
        $res->close();
      }
    }
    
    public function getAuthors() {
      return $this->_authors;
    }
    
    public function getActiveAuthors() {
      return $this->_activeAuthors;
    }
  }
