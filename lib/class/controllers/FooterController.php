<?php
  namespace controllers;
  
  class FooterController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Footer', $engine);
    }
  }
