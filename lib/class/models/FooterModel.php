<?php
  namespace models;
  
  class FooterModel extends WrapperModel {
    private $_menu;
    
    public function __construct() {
      parent::__construct('SYS_FOOTER_ADDITIONS');
    }
    
  }
