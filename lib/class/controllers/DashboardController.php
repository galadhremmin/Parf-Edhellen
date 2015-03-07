<?php
  namespace controllers;
  
  class DashboardController extends SecureController {
    public function __construct(\TemplateEngine $engine) {
      parent::__construct('Dashboard', $engine);
    }
  }
  
