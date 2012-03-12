<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageResourcesController extends Controller {
    public function __construct(TemplateEngine &$engine) {
      parent::__construct('resources');
    }
  }