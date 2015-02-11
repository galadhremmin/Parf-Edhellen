<?php
  namespace controllers;
  
  class ResourcesController extends Controller {
    public function __construct(\TemplateEngine &$engine) {
      parent::__construct('Resources', $engine);
    }
  }
