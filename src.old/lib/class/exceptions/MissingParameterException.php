<?php
  namespace exceptions;
  
  class MissingParameterException extends \ErrorException {
    public function __construct($paramName) {
      parent::__construct('Missing parameter "'.$paramName.'".');
    }
  }
?>
