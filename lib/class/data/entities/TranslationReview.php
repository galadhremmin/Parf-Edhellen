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

    public function __construct($data = null) {
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
          'justification' => null
        );
      } else if (is_array($data)) {
        // Convert the service request data array to an initializion array which the parent
        // constructor understands. Assume that validation has already been performed...
        // this is a bit hacky..! :(
        $data = array(
          'reviewID'      => 0,
          'languageID'    => $data['language'],
          'dateCreated'   => ElfyDateTime::now(),
          'word'          => $data['word'],
          'data'          => $data,
          'reviewed'      => null,
          'reviewedBy'    => null,
          'approved'      => false,
          'justification' => null
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
        'SELECT `AuthorID`, `LanguageID`, `DateCreated`, `Word`, `Data`, `Reviewed`, `ReviewedBy`, `Approved`, `Justification`
           FROM `translation_review`
           WHERE `ReviewID` = ?');
      $stmt->bind_param('i', $numericId);
      $stmt->execute();
      $stmt->bind_result(
        $this->authorID, $this->languageID, $dateCreated, $this->word, $data,
        $reviewed, $this->reviewedBy, $approved, $this->justification
      );
      if ($stmt->fetch()) {
        $this->data = unserialize($data);
        $this->dateCreated = new DateTime($dateCreated);
        $this->reviewed = new DateTime($reviewed);
        $this->approved = ($approved == 1);
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
      \auth\Credentials::request(new \auth\TranslationReviewAccessRequest($this->reviewID));

      // prepare some variables which require formatting before being inserted to the SQL query
      $data        = serialize($this->data);
      $approved    = $this->approved ? 1 : 0;
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
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->bind_param('iissssiis', $this->authorID, $this->languageID, $dateCreated, $this->word, $data, $reviewed,
          $this->reviewedBy, $approved, $this->justification);
        $stmt->execute();
        $this->reviewID = $stmt->insert_id;
        $stmt = null;
      } else {
        $stmt = $db->prepare(
          'UPDATE `translation_review` SET `Data` = ?, `Reviewed` = ?, `ReviewedBy` = ?, `Approved` = ?, `Justification = ?
           WHERE `ReviewID` = ?'
        );

        $stmt->bind_param('ssiisi', $data, $reviewed, $this->reviewedBy, $approved, $this->justification, $this->reviewID);
        $stmt->execute();
        $stmt = null;
      }

      return $this;
    }
  }