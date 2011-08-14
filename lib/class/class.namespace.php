<?php
  class Namespace extends Entity {
    public $identifier;
  
    public function validate() {
      throw new NotImplementedException('validate');
    }
    
    public function load($id) {
      $conn = Database::instance();
      
      $stmt = $conn->connection()->prepare(
        'SELECT `Identifier` FROM `namespace` WHERE `NamespaceID` = ?'
      );
      $stmt->bind_param('i', $id);
      $stmt->execute();
      $stmt->bind_result($this->identifier);
      $stmt->fetch();
      $stmt->close();
      
      return $this;
    }
    
    public function save() {
      throw new NotImplementedException('save');
    }
  }
?>