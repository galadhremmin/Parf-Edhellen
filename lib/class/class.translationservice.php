<?php
  class TranslationService extends RESTfulService {
    public function handleRequest(&$data) {
      throw new ErrorException('Parameterless request presently unsupported.');
    }
    
    public function handleParameterizedRequest(&$data, $param = null) {
      if (is_numeric($param)) {
        return self::getTranslation($param);
      }
      
      switch ($param) {
        case 'register':
          return self::registerTranslation($data);

        default:
          throw new ErrorException("Unrecognised command '".$param."'.");
      }
    }
    
    private function getTranslation($id) {
      // result container
      $data = null;
      
      $db = Database::instance();
      $query = $db->connection()->prepare(
        'SELECT 
          t.`LanguageID`, t.`Translation`, t.`Etymology`, t.`Type`, t.`Source`, t.`Comments`, 
          t.`WordID`, t.`Latest`, t.`DateCreated`, t.`AuthorID`, t.`Tengwar`, t.`Gender`, t.`Phonetic`, 
          w.`Key`, w.`KeyID`, t.`NamespaceID`
         FROM `translation` t 
         LEFT JOIN `word` w ON w.`KeyID` = t.`WordID`
         WHERE t.`TranslationID` = ?'
      );

      $query->bind_param('i', $id);
      $query->execute();
      $query->bind_result(
        $languageID, $translation, $etymology, $type, $source, $comments,
        $wordID, $latest, $dateCreated, $authorID, $tengwar, $gender, $phonetic,
        $word, $wordID, $namespaceID
      );
      
      if ($query->fetch()) {
        $data = array(
          'id'          => $id,
          'language'    => $languageID,
          'translation' => $translation,
          'etymology'   => $etymology,
          'type'        => $type,
          'source'      => $source,
          'comments'    => $comments,
          'wordID'      => $wordID,
          'latest'      => $latest,
          'dateCreated' => $dateCreated,
          'authorID'    => $authorID,
          'tengwar'     => $tengwar,
          'gender'      => $gender,
          'phonetic'    => $phonetic,
          'word'        => $word,
          'wordID'      => $wordID,
          'namespaceID' => $namespaceID
        );
      }
      
      $query->close();
      
      return $data;
    }
    
    private function registerTranslation(&$data) {
      $values = array(
        'type'        => array_keys(Translation::getTypes()),
        'namespaceID' => '/^[0-9]+$/',
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
          throw new Exception('Missing parameter: '.$key);
        }
      
        $value = $data[$key];
      
        if ($validation !== null) {
          if ((is_array($validation) && !in_array($value, $validation)) ||
              (is_string($validation) && !preg_match($validation, $value))) {
            throw new Exception('Malformed parameter: '.$key);
          }
        }
      
        $values[$key] = stripslashes($value);
      }
    
      $translationObj = new Translation($values);
      return Word::registerTranslation($translationObj);
    }
  }
?>