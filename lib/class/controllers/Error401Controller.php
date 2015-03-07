<?php
  namespace controllers;
  
  class Error401Controller extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Error', $engine);
    }
  }
