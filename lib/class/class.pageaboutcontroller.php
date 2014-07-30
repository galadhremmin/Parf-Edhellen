<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageAboutController extends Controller {
    public function __construct($engine) {
      parent::__construct('about', $engine);
    }
  }
?>
