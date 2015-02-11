<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class InvalidParameterException extends ErrorException {
    public function __construct($paramName) {
      parent::__construct('Invalid parameter "'.$paramName.'".');
    }
  }
?>