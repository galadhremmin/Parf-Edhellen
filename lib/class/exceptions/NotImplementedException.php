<?php
  namespace exceptions;

  class NotImplementedException extends \ErrorException {
    public function __construct($method) {
      parent::__construct('The method "'.$method.'" lacks implementation.');
    }
  }
?>
