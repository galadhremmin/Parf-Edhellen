<?php
  if (!defined('SYS_ACTIVE')) {
    exit;
  }
  
  abstract class ExperienceMessageType {
    const Message = 'message';
    const Information = 'information';
    const Error = 'error';
    const None = null;
  }