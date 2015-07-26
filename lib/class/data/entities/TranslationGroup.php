<?php

  namespace data\entities;

  class TranslationGroup extends Entity {

    public $id;
    public $name;
    public $canon;
    public $externalLinkFormat;

    public static function emptyGroup() {
      return new TranslationGroup(array('id' => null));
    }

    public static function getAllGroups() {
      $db = \data\Database::instance()->connection();
      $res = $db->query('SELECT `TranslationGroupID`, `Name`, `Canon` FROM `translation_group` ORDER BY `Name` ASC');

      $groups = array();
      while ($row = $res->fetch_assoc()) {
        $groups[$row['TranslationGroupID']] = $row['Name'];
      }

      $res->free_result();
      $res = null;

      return $groups;
    }

    public function __construct($data = null) {
      $this->externalLinkFormat = null;
      $this->name = null;

      parent::__construct($data);

      if ($this->id === null) {
        // emtpy group -- this group doesn't actually exist, so assign default values instead.
        $this->canon = false;
      }
    }

    public function validate() {
      if ($this->id == 0) {
        return false;
      }

      return true;
    }

    public function load($numericId) {
      // TODO: Implement load() method.
    }

    public function save() {
      // TODO: Implement save() method.
    }
  }