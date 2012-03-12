<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  class ExperienceResponse {
    public $nick;
    public $message;
    public $messageType;
    
    public function __construct($nick, $message, $messageType) {
      $this->nick        = $nick;
      $this->message     = $message;
      $this->messageType = $messageType;
    }
  }