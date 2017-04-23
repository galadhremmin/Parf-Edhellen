<?php
  namespace controllers;
  
  class AboutController extends Controller {
    public function __construct(\TemplateEngine $engine) {
      parent::__construct('About', $engine);
    }
  }
  
