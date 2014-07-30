<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class PageFooterController extends Controller {
    public function __construct(TemplateEngine &$engine) {
      parent::__construct('footer', $engine);
    }
  }
?>
