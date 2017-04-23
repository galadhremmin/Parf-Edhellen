<?php
  namespace utils;
  use \data\entities\Translation as Translation;

  class TranslationComparer {  
    public static function compare(Translation $a, Translation $b) {
      if ($a->id == $b->id) {
        return 0;
      }

      if ($a->rating == $b->rating) {
        return strcmp($a->word, $b->word) < 0 ? -1 : 1;
      }

      return $a->rating - $b->rating < 0 ? 1 : -1; 
    }
  }  
