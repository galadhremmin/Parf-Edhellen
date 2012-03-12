<?php
  
  class ExperienceMessage extends ExperienceResponse {
    public $messageId;
  
    public function __construct($nick, $message, $messageType, $messageId) {
      parent::__construct($nick, $message, $messageType);
      $this->messageId = $messageId;
    }
  }