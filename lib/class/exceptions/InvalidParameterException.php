<?php
  namespace exceptions;
  
  class InvalidParameterException extends \ErrorException {
    public function __construct($paramName) {
      parent::__construct('Invalid parameter "'.$paramName.'".');
    }
  }
?>
