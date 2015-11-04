<?php

  namespace data\entities;

  use utils\ElfyDateTime;

  class TranslationReview extends Entity {

    /**
     * @var Primary key for the TranslationReview entity.
     */
    public $reviewID;
    /**
     * @var Numeric ID for the author.
     */
    public $authorID;
    /**
     * @var The name of the author
     */
    public $authorName;
    /**
     * @var Numeric ID for the language.
     */
    public $languageID;
    /**
     * @var The datetime the review was requested.
     */
    public $dateCreated;
    /**
     * @var Title, usually the gloss.
     */
    public $word;
    /**
     * @var A serializable instance of the data to be reviewed.
     */
    public $data;
    /**
     * @var Datetime when the review was carried out.
     */
    public $reviewed;
    /**
     * @var Numeric ID for the administrator who performed the review.
     */
    public $reviewedBy;
    /**
     * @var Approved flag.
     */
    public $approved;
    /**
     * @var Short message from the administrator justifying the verdict. Feedback.
     */
    public $justification;
    /**
     * @var Unique identifier for the translation entity which this review item created upon being approved.
     */
    public $translationID;

    public static function getByAccount(Account $account) {
      $db = \data\Database::instance()->connection();
      $reviews = array();

      $stmt = $db->prepare(
        "SELECT `ReviewID`, `LanguageID`, `DateCreated`, `Word`, `Approved`, `Justification`
         FROM  `translation_review` WHERE `AuthorID` = ?
         ORDER BY `DateCreated` DESC"
      );

      $stmt->bind_param('i', $account->id);
      $stmt->execute();
      $stmt->bind_result($id, $languageID, $datedCreated, $word, $approved, $justification);

      while ($stmt->fetch()) {
        $reviews[] = new TranslationReview(array(
          'reviewID'      => $id,
          'authorID'      => $account->id,
          'languageID'    => $languageID,
          'dateCreated'   => ElfyDateTime::parse($datedCreated),
          'word'          => $word,
          'approved'      => ($approved === null ? null : ($approved === 1)),
          'justification' => $justification
        ));
      }

      $stmt->free_result();
      $stmt = null;

      return $reviews;
    }

    public static function getPendingReviews($from = -1, $to = -1) {
      if (! is_numeric($from) || ! is_numeric($to)) {
        throw new \exceptions\InvalidParameterException('pagination offsets');
      } else {
        $from = intval($from);
        $to   = intval($to);
      }

      $db = \data\Database::instance()->connection();
      $reviews = array();

      $stmt = $db->query(
        \data\SqlHelper::paginate(
          "SELECT `ReviewID`, `AuthorID`, `LanguageID`, `DateCreated`, `Word`
           FROM  `translation_review` WHERE `Approved` IS NULL
           ORDER BY `DateCreated` ASC", $from, $to
        )
      );

      while ($row = $stmt->fetch_assoc()) {
        $reviews[] = new TranslationReview(array(
          'reviewID'    => $row['ReviewID'],
          'authorID'    => $row['AuthorID'],
          'languageID'  => $row['LanguageID'],
          'dateCreated' => ElfyDateTime::parse($row['DateCreated']),
          'word'        => $row['Word']
        ));
      }

      $stmt->free();
      $stmt = null;

      return $reviews;
    }

    public static function getLatestReviewsApproved($max = 10) {
      if (!is_numeric($max)) {
        throw new \exceptions\InvalidParameterException('max');
      }

      $db = \data\Database::instance()->connection();
      $reviews = array();

      $stmt = $db->query(
        \data\SqlHelper::paginate(
          "SELECT t.`AuthorID`, t.`LanguageID`, t.`DateCreated`, t.`Word`, t.`TranslationID`, a.`Nickname`
           FROM  `translation_review` t
             INNER JOIN `translation` t0 ON
              (t0.`TranslationID` = t.`TranslationID` OR t0.`EldestTranslationID` = t.`TranslationID`)
              AND t0.`Deleted` = '0' AND t0.`Latest` = '1'
             INNER JOIN `auth_accounts` a ON a.`AccountID` = t.`AuthorID`
           WHERE t.`Approved` = b'1'
           ORDER BY t.`DateCreated` DESC", 0, $max
        )
      );

      while ($row = $stmt->fetch_assoc()) {
        $reviews[] = new TranslationReview(array(
          'authorID'      => $row['AuthorID'],
          'languageID'    => $row['LanguageID'],
          'dateCreated'   => ElfyDateTime::parse($row['DateCreated']),
          'word'          => $row['Word'],
          'translationID' => $row['TranslationID'],
          'authorName'    => $row['Nickname']
        ));
      }

      $stmt->free();
      $stmt = null;

      return $reviews;
    }

    public function __construct($data = null, $rawRequest = false) {
      // Always set default approved to null by default.
      $this->approved = null;

      if ($data instanceof Translation) {
        // Convert the Translation object to an initialization array which the parent
        // constructor understands.
        $data = array(
          'reviewID'      => 0,
          'languageID'    => $data->language,
          'dateCreated'   => ElfyDateTime::now(),
          'word'          => $data->word,
          'data'          => $data,
          'reviewed'      => null,
          'reviewedBy'    => null,
          'approved'      => false,
          'justification' => null,
          'translationID' => null
        );
      } else if (is_array($data) && $rawRequest) {
        // Convert the service request data array to an initializion array which the parent
        // constructor understands. Assume that validation has already been performed...
        $data = array(
          'reviewID'      => 0,
          'languageID'    => $data['language'],
          'dateCreated'   => ElfyDateTime::now(),
          'word'          => $data['word'],
          'data'          => $data,
          'reviewed'      => null,
          'reviewedBy'    => null,
          'approved'      => false,
          'justification' => null,
          'translationID' => null
        );
      }

      parent::__construct($data);

      if ($this->authorID == 0) {
        $account = \auth\Credentials::current()->account();
        $this->authorID = $account->id;
      }
    }

    public function validate() {
      if (! is_numeric($this->authorID) || $this->authorID === 0) {
        throw new \exceptions\InvalidParameterException('authorID');
      }

      if (is_null($this->data) || empty($this->data)) {
        throw new \exceptions\InvalidParameterException('data');
      }

      if (is_null($this->word) || empty($this->word)) {
        throw new \exceptions\InvalidParameterException('word');
      }

      if (! is_numeric($this->languageID) || $this->languageID === 0) {
        throw new \exceptions\InvalidParameterException('languageID');
      }

      return true;
    }

    public function load($numericId) {
      $db = \data\Database::instance()->connection();

      $stmt = $db->prepare(
        'SELECT `AuthorID`, `LanguageID`, `DateCreated`, `Word`, `Data`, `Reviewed`, `ReviewedBy`, `Approved`, `Justification`,
           `TranslationID`
           FROM `translation_review`
           WHERE `ReviewID` = ?');
      $stmt->bind_param('i', $numericId);
      $stmt->execute();
      $stmt->bind_result(
        $this->authorID, $this->languageID, $dateCreated, $this->word, $data,
        $reviewed, $this->reviewedBy, $approved, $this->justification, $this->translationID
      );
      if ($stmt->fetch()) {
        $this->data = unserialize($data);
        $this->dateCreated = ElfyDateTime::parse($dateCreated);
        $this->reviewed = ElfyDateTime::parse($reviewed);
        $this->approved = ($approved === null ? null : ($approved === 1));
        $this->reviewID = $numericId;
      } else {
        $this->reviewID = 0;
      }
      $stmt = null;

      return $this;
    }

    /**
     * Saves a copy of the translation review object, or updates the existing one.
     *
     * @return $this
     * @throws \exceptions\InadequatePermissionsException
     * @throws \exceptions\InvalidParameterException
     */
    public function save() {
      // ensure that the input is valid
      $this->validate();

      // only administrators might change existing reviews
      \auth\Credentials::request(new \auth\BasicAccessRequest());
      $fullAccess = \auth\Credentials::permitted(new \auth\TranslationReviewAccessRequest($this->reviewID));

      // prepare some variables which require formatting before being inserted to the SQL query
      $data        = serialize($this->data);
      $approved    = $this->approved ? 1 : ($this->approved === null ? null : 0);
      $dateCreated = ElfyDateTime::toLongDateString( ElfyDateTime::parseOrToday($this->dateCreated) );
      $reviewed    = ElfyDateTime::parse($this->reviewed);
      if ($reviewed !== null) {
        $reviewed = ElfyDateTime::toLongDateString($reviewed);
      }

      $db = \data\Database::instance()->connection();

      if ($this->reviewID === 0) {
        $stmt = $db->prepare(
          'INSERT INTO `translation_review`
          (`AuthorID`,  `LanguageID`, `DateCreated`, `Word`, `Data`, `Reviewed`, `ReviewedBy`, `Approved`, `Justification`)
          VALUES (?, ?, ?, ?, ?, ?, ?, NULL, ?)'
        );

        $stmt->bind_param('iissssisi', $this->authorID, $this->languageID, $dateCreated, $this->word, $data, $reviewed,
          $this->reviewedBy, $this->justification, $this->translationID);
        $stmt->execute();
        $this->reviewID = $stmt->insert_id;
        $stmt = null;
      } else if ($fullAccess) {
        $stmt = $db->prepare(
          'UPDATE `translation_review` SET `Data` = ?, `Reviewed` = ?, `ReviewedBy` = ?, `Approved` = ?, `Justification` = ?,
                  `TranslationID` = ?
           WHERE `ReviewID` = ?'
        );

        $stmt->bind_param('ssiisii', $data, $reviewed, $this->reviewedBy, $approved, $this->justification,  $this->translationID,
          $this->reviewID);
        $stmt->execute();
        $stmt = null;
      } else {
        $stmt = $db->prepare(
          'UPDATE `translation_review` SET `Data` = ?
           WHERE `ReviewID` = ? AND `Approved` IS NULL'
        );

        $stmt->bind_param('si', $data, $this->reviewID);
        $stmt->execute();
        $stmt = null;
      }

      return $this;
    }

    public function approve($translationID) {
      $this->approved = true;
      $this->reviewedBy = \auth\Credentials::current()->account()->id;
      $this->reviewed = ElfyDateTime::now();
      $this->translationID = $translationID;

      if (empty($this->justification)) {
        $this->justification = 'OK';
      }

      $this->save();
    }

    public function reject() {
      $this->approved = false;
      $this->reviewedBy = \auth\Credentials::current()->account()->id;
      $this->reviewed = ElfyDateTime::now();

      $this->save();
    }

    public function delete() {
      $db = \data\Database::instance()->connection();
      $stmt = $db->prepare('DELETE FROM `translation_review` WHERE `ReviewID` = ?');
      $stmt->bind_param('i', $this->reviewID);
      $stmt->execute();
      $stmt = null;
    }
  }
