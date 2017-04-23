<?php
  namespace exceptions;
  
  class ValidationException extends \ErrorException {
    public function __construct($className) {
      base::__construct($className.' failed to validate.');
    }
  }