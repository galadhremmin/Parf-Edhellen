<?php
  namespace controllers;
  
  class ErrorController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Error', $engine);
    }
  }
