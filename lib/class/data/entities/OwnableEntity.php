<?php
  namespace data\entities;
  
  abstract class OwnableEntity extends Entity {
    public $ownerId;
    
    public function setOwner(Account &$owner) {
      if ($owner === null) {
        $this->ownerId = 0;
      } else {
        $this->ownerId = $owner->id;
      }
    }
  }
