<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class MissingParameterException extends ErrorException {
    public function __construct($paramName) {
      parent::__construct('Missing parameter "'.$paramName.'".');
    }
  }
?>