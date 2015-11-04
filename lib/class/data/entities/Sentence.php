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

  public static function getRandomSentence() {
    $db = \data\Database::instance();

    $stmt = $db->connection()->query(
      'SELECT `SentenceID`
         FROM `sentence`
         ORDER BY RAND()
         LIMIT 1'
    );

    $sentence = new Sentence();
    if ($row = $stmt->fetch_assoc()) {
      $stmt->free_result();
      $stmt = null;

      $sentence->load($row['SentenceID']);
      $sentence->create(false);
    }

    return $sentence;
  }

  public function __construct($data = array()) {
    $this->fragments = array();
    $this->sentence = '';

    parent::__construct($data);
  }

  public function validate() {
  }

  public function load($numericId) {
    $db = \data\Database::instance();

    $stmt = $db->connection()->prepare(
      'SELECT s.`SentenceID`, s.`Source`, s.`Description`, l.`Name` AS `Language`, s.`LanguageID`,
                f.`Fragment`, f.`Tengwar`, f.`TranslationID`, f.`FragmentID`,
                f.`Comments`
         FROM `sentence` s
           INNER JOIN `language` l ON l.`ID` = s.`LanguageID`
           INNER JOIN `sentence_fragment` f ON f.`SentenceID` = s.`SentenceID`
         WHERE s.`SentenceID` = ?'
    );
    $stmt->bind_param('i', $numericId);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result(
      $id, $source, $description, $languageName, $languageID,
      $fragment, $fragmentTengwar, $fragmentTranslationID, $fragmentID, $fragmentComments);

    $initialized = false;
    $sentenceFragments = array();
    while ($stmt->fetch()) {
      if (! $initialized) {
        $this->ID          = $id;
        $this->language    = new Language(array('id' => $languageID, 'name' => $languageName));
        $this->description = $description;
        $this->source      = $source;
        $this->fragments   = array();

        $initialized = true;
      }

      $this->fragments[] = new SentenceFragment($fragmentID, $fragment, $fragmentTengwar, $fragmentTranslationID, $fragmentComments);
      $sentenceFragments[] = $fragment;
    }

    $this->sentence = implode(' ', $sentenceFragments);

    $stmt->free_result();
    $stmt = null;
  }

  public function save() {
  }

  public function create($withLinks = true) {
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
        if ($withLinks) {
          $html = '<a href="#" id="ed-fragment-' . $fragment->fragmentID .
            '" data-fragment-id="' . $fragment->fragmentID .
            '" data-translation-id="' . $fragment->translationID .
            '">' . $fragment->fragment . '</a>';
        } else {
          $html = $fragment->fragment;
        }
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
    $db = \data\Database::instance()->connection();

    $query = $db->prepare('UPDATE `sentence_fragment` SET `TranslationID` = ? WHERE `TranslationID` = ?');
    $query->bind_param('ii', $trans->id, $id);
    $query->execute();
    $query->close();
  }
}
