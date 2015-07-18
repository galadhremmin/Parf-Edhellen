<?php
  namespace services;
  
  class TranslationService extends ServiceBase {
    public function __construct() {
      parent::__construct();
      
      parent::registerMainMethod('getTranslation');
      parent::registerMethod('save', 'registerTranslation');
      parent::registerMethod('translate', 'translate');
      parent::registerMethod('saveReview', 'saveReview');
      parent::registerMethod('deleteReview', 'deleteReview');
    }
    
    public function handleRequest(&$data) {
      throw new \ErrorException('Parameterless request presently unsupported.');
    }
    
    protected static function getTranslation($id) {
      $t = new \data\entities\Translation();
      $t->load($id);
      $t->transformContent();
      
      if ($t->index) {
        $t = null;
      }
      
      return $t;
    }
    
    protected static function registerTranslation(&$data) {

      // Only administrators are permitted to make changes to the dictionary. Everybody else
      // must create a review item instead.
      $request = new \auth\TranslationAccessRequest($data['id']);
      try {
        \auth\Credentials::request($request);
      } catch (\exceptions\InadequatePermissionsException $ex) {

        // Check whether the current set of credentials are eligible for creating review
        // items.
        if ($request->requiresReview()) {
          // Create a translation review instead, and return it. The client must be able to
          // distinguish between these two classes, should it chose to do anything with the
          // resulting object.
          $review = new \data\entities\TranslationReview($data, true);
          return $review->save();
        }
      }

      // Register the translation to the database. This method typically only invoked directly by
      // administrators of the site, unless the default access right sets have been modified.
      $result = self::saveTranslation($data);
      return $result;
    }
    
    protected static function translate(&$input) {    
      if (!isset($input['term']) || !is_string($input['term'])) {
        throw new \Exception("Missing parameter 'term'.");
      }

      return \data\entities\Translation::translate($input['term'], null);
    }

    protected static function saveReview(&$input) {

      // The reviewID paramteter is the numeric identification key for the review item. This is a
      // required parameter, which means that you cannot create new review items by invoking saveReview.
      if (!isset($input['reviewID']) || !is_numeric($input['reviewID'])) {
        return false;
      }
      $reviewID = intval($input['reviewID']);

      // If the reviewUpdate parameter is set to true, the user which submitted the review item
      // requests to make changes to it. This should be possible until the point at which the item
      // has been approved, rejected or deleted.
      $justUpdate = isset($input['reviewUpdate']) && boolval($input['reviewUpdate']) === true;

      if ($justUpdate) {

        // Request basic credentials for this operation.
        $credentials = \auth\Credentials::request(new \auth\BasicAccessRequest());

        // Load the review item and reload the information.
        $review = new \data\entities\TranslationReview($input, true);
        $review->reviewID = $reviewID;

        // Only permit owners of the review item to perform changes to it... and administrators, of course.
        if ($credentials->account()->isAdministrator() ||
            $credentials->account()->id === $review->authorID) {
          $review->save();
        }

      } else {
        $approved = boolval($input['reviewApproved']);

        // Administrators doesn't have to justify their decision. This parameter is therefore
        // optional.
        $justific = isset($input['reviewJustification']) ? $input['reviewJustification'] : null;

        // Check for permissions to make changes to the review
        \auth\Credentials::request(new \auth\TranslationReviewAccessRequest($reviewID));

        // Attempt to load the review
        $review = new \data\entities\TranslationReview();
        $review->load($reviewID);

        if (! $review->validate()) {
          // Load failed -- quit!
          return false;
        }

        if ($approved) {
          self::saveTranslation($review);
          $review->justification = $justific;
          $review->approve();
        } else {
          $review->justification = $justific;
          $review->reject();
        }
      }

      return true;
    }

    public static function saveTranslation($source) {

      // Flag  which specifies whether the source is a review item.
      $review = false;

      if ($source instanceof \data\entities\TranslationReview) {
        // A translation item has been passed as the source argument to this method.
        // Please note that this implies that the review item has been approved and
        // is ready to be moved into the dictionary.
        $data = $source->data;
        $review = true;
      } else if (is_array($source)) {
        // Raw request data -- assume it's the information required for a translation.
        $data = $source;
      } else {
        throw new Exception('Unrecognised source.');
      }

      $values = self::getValues($data);

      // Create a sense, if one isn't specified. Base the sense on the gloss.
      $ns = new \data\entities\Sense();
      $ns->load($values['senseID']);
      if ($ns->id == 0) {
        $ns->identifier = $values['translation'];
        $ns->save();
        $values['senseID'] = $ns->id;
      }

      // register translations
      $translationObj = new \data\entities\Translation($values);

      if ($review) {
        // Create an auxiliary set of credentials for the original author of the reviewed
        // translation item, in order to ensure that the item is attributed the correct
        // author
        $cred = \auth\Credentials::copyFor($source->authorID);

        // Transfer the reviewed translation to the dictionary.
        $result = $translationObj->transfer($cred);
      } else {
        // Save the translation item to the dictionary.
        $result = $translationObj->save();
      }

      // One or several indexes might be optionally attributed to the translation item.
      // Save these as well. The current set of credentials are assumed to be the author
      // of these indexes.
      if (isset($data['indexes']) && is_array($data['indexes'])) {
        foreach ($data['indexes'] as $indexWord) {
          $index = new \data\entities\Translation(array(
            'word'     => $indexWord,
            'language' => $translationObj->language,
            'senseID'  => $ns->id
          ));

          $index->saveIndex();
        }
      }

      return $result;
    }

    public static function deleteReview(&$data) {
      if (! isset($data['reviewID'])) {
        throw new \exceptions\InvalidArgumentException('reviewID');
      }

      $reviewID = intval($data['reviewID']);
      \auth\Credentials::request(new \auth\TranslationReviewAccessRequest($reviewID));

      $review = new \data\entities\TranslationReview(array('reviewID' => $reviewID));
      $review->delete();
    }

    private static function getValues(&$data) {
      // retrieve values. The key maps to the REQUEST variables expected, and the
      // value defines what sort of values to be expect.
      $values = array(
        'type'        => array_keys(\data\entities\Translation::getTypes()),
        'senseID'     => '/^[0-9]+$/',
        'id'          => '/^[0-9]+$/',
        'language'    => '/^[0-9]+$/',
        'word'        => null,
        'translation' => null,
        'etymology'   => null,
        'source'      => null,
        'comments'    => null,
        'tengwar'     => null,
        'phonetic'    => null
      );

      foreach ($values as $key => $validation) {
        if (!isset($data[$key])) {
          throw new \Exception('Missing parameter: '.$key);
        }

        $value = $data[$key];

        if ($validation !== null) {
          if ((is_array($validation) && !in_array($value, $validation)) ||
            (is_string($validation) && !preg_match($validation, $value))) {

            if (is_array($validation)) {
              $validationValues = implode(', ', $validation);
            }

            throw new \Exception('Malformed parameter: '.$key.'. Received "'.$value.'", expected '.$validationValues);
          }
        }

        $values[$key] = stripslashes($value);
      }

      return $values;
    }
  }
