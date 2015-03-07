<?php
  namespace exceptions;

  class InadequatePermissionsException extends \ErrorException {
    public function __construct($method) {
      parent::__construct('Inadequate permissions for '.$method);
    }
  }
?>
